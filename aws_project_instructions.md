# Scalable AWS Cloud Native Web Application Architecture

## 📌 Project Overview
This project demonstrates an enterprise-grade, auto-scaling, and high-availability cloud architecture deployed on AWS. The application allows users to submit visitor details alongside image uploads. 

The underlying infrastructure leverages **Serverless processing** (AWS Lambda) for on-the-fly image resizing, **Shared File Storage** (AWS EFS) and **Managed Relational Databases** (AWS RDS) for persistent data storage, **Auto Scaling Groups (ASG)** behind an **Application Load Balancer (ALB)** for compute elasticity, and **AWS CloudFront** with **Route 53** for global edge delivery.

---

## 📐 Architecture Diagram & Workflow

```
                        [ User / Client ]
                                |
                                v
                           [ Route 53 ]
                                |
                                v
                     [ CloudFront CDN ]
                                |
                                v
               [ Application Load Balancer (ALB) ]
                                |
             +------------------+------------------+
             |                 |                   |
             v                 v                   v
      [ EC2 Instance ]  [ EC2 Instance ]   [ EC2 Instance ] (Auto Scaling Group)
             |                 |                   |
             +--------+--------+-------------------+
                      |
        +-------------+-------------+--------------------+
        |                           |                    |
        v                           v                    v
  [ AWS EFS ]              [ AWS RDS (MySQL) ]   [ S3 Source Bucket ]
  (Shared /var/www/html)   (Multi-AZ DB)                 |
                                                         v (Object Created)
                                                 [ AWS Lambda ]
                                                 (Resizes to 100x100)
                                                         |
                                                         v
                                              [ S3 Resized Bucket ]
```

---

## 🛠 AWS Services & Technologies Used

* **Compute:** AWS EC2 (Amazon Linux 2), Auto Scaling Group (ASG), AWS Lambda (Node.js)
* **Networking & Content Delivery:** VPC, Public Subnets, Security Groups, Application Load Balancer (ALB), CloudFront CDN, Route 53
* **Storage:** Amazon S3, Amazon EFS (Elastic File System)
* **Database:** Amazon RDS (MySQL, Multi-AZ / Read Replica configuration)
* **Security & IAM:** AWS IAM Roles (`Lambda-S3`, `Ec2-S3`)
* **Application Stack:** PHP 7.2, Apache (`httpd`), MySQL Client, Shell Scripting, Cron

---

## 🚀 Key Deployment Steps

### 1. Storage & Serverless Pipeline
* **S3 Buckets:** Created a primary bucket (`projeisminiz`) for raw image uploads and a secondary public bucket (`projeisminiz-resized`) with CORS enabled for resized output.
* **AWS Lambda:** Created a Node.js Lambda function attached to an S3 event trigger (`All Object Create Events`). When a file is uploaded to `projeisminiz/images`, Lambda resizes it to 100x100 and moves it to `projeisminiz-resized`.

### 2. Base Compute Template & PHP Stack
* Provisioned a baseline **EC2 T2.Micro** instance attached to the `Ec2-S3` IAM role and `Proje-SecGroup` security group.
* Installed Apache (`httpd`), MySQL client, and PHP 7.2.
* Verified PHP execution via `phpinfo()`.

### 3. Shared File System (EFS) Integration
* Provisioned an **Amazon EFS** file system across all public subnets with NFS inbound permissions (`Port 2049`) for `Proje-SecGroup`.
* Mounted EFS to `/var/www/html` on the EC2 template and configured `/etc/fstab` for persistent auto-mounting across reboots.
* Configured image upload directories with `chmod 777` permissions for PHP file handling.

### 4. Database Setup (RDS)
* Created a Multi-AZ **Amazon RDS (MySQL)** instance (`projedbinstance`).
* Restricted access via RDS Security Groups to accept inbound traffic on `Port 3306` only from `Proje-SecGroup`.
* Executed DDL script to create the core data table:
  ```sql
  CREATE DATABASE proje;
  USE proje;
  CREATE TABLE visitors (
      name VARCHAR(30),
      email VARCHAR(30),
      phone VARCHAR(30),
      photo VARCHAR(30)
  );
  ```

### 5. Application Code & S3 Sync Automation
* Deployed PHP scripts (`add.php`, `view.php`, `index.html`) configured with the RDS endpoint.
* Created a background sync shell script (`s3.sh`):
  ```bash
  #!/bin/bash
  aws s3 sync /var/www/html/images s3://projeisminiz/images
  ```
* Configured `crontab` to trigger S3 synchronization every 2 minutes:
  ```cron
  */2 * * * * /var/www/html/s3.sh
  ```

### 6. Golden AMI & Elastic Auto Scaling
* Stopped the template machine, created an Elastic Block Store (EBS) snapshot, and registered a custom AMI (`ProjeAMI`).
* Configured an **Application Load Balancer (ALB)** with health checks on `/index.html`.
* Created an **Auto Scaling Group (ASG)** using a Launch Configuration linked to `ProjeAMI`:
  * **Min / Desired Capacity:** 3 instances
  * **Max Capacity:** 5 instances
  * **Target Tracking Policy:** Scale out when Average CPU Utilization exceeds 90%.

### 7. Global Distribution & DNS Mapping
* Deployed an **Amazon CloudFront Web Distribution** pointing to the ALB origin to accelerate dynamic content delivery worldwide.
* Mapped custom domain traffic (`www.yourdomain.com`) to the CloudFront distribution via an **AWS Route 53** Alias A Record.

---

## 🔍 Verification & Testing

1. **Database & Form Submission:** Navigated to the ALB endpoint / custom domain, submitted visitor records and image files, and verified DB row creation.
2. **Automated Resizing Pipeline:** Verified that cron triggers `s3.sh`, uploading images to the source bucket, executing the Lambda function, and outputting thumbnails in `projeisminiz-resized`.
3. **High Availability & Health Checks:** Confirmed all 3 ASG instances registered as `Healthy` under the Target Group metrics.

---

## ⚙️ Configuration Details Quick Reference

| Resource | Identifier / Setting |
| :--- | :--- |
| **Source S3 Bucket** | `projeisminiz` |
| **Resized S3 Bucket** | `projeisminiz-resized` |
| **Security Group** | `Proje-SecGroup` (Ports: 80, 22) |
| **IAM Lambda Role** | `Lambda-S3` (`AWSLambdaExecute`) |
| **IAM EC2 Role** | `Ec2-S3` (`AWSS3FullAccess`) |
| **RDS Endpoint** | `projedbinstance...` (Port 3306) |
| **Target Group Path**| `/index.html` |