# AWS Project Requirements

## VPC Requirement

**1:** If you deleted the VPC we created in the VPC section of the training, go back to that section and follow the steps again to create a VPC consisting of **3 Public Subnets**.

---

# Creating the S3 Buckets

We will create two S3 buckets to store the images uploaded by our application. The first bucket will store the images, while the second bucket will store the resized copies of the images created by our Lambda function.

**2:** Go to the S3 service and create a bucket named `projeisminiz`. Also create a folder named `images` inside it.

**3:** Go to the S3 service and create another bucket named `projeisminiz-resized` — meaning that the name of the S3 bucket you created previously is followed by a hyphen `-` and then `resized`. **This naming is important.**

When creating the bucket, in Step 2, **Configure options**, remove all four check marks under the following options:

* **Manage public access control lists (ACLs) for this bucket**
* **Manage public bucket policies for this bucket**

Immediately afterward, create and configure the required **Bucket Policy** so that all objects uploaded to this S3 bucket have public access.

Then go to the **CORS** settings and configure it to allow the following methods from the `*` origin:

* GET
* PUT
* POST

---

# Creating the IAM Roles

We are now creating the IAM roles that will be needed throughout the project.

**4:** Go to the IAM service and create **2 IAM roles**.

### Lambda-S3

* Name: `Lambda-S3`
* Service: **Lambda**
* Policy: **AWSLambdaExecute**

### Ec2-S3

* Name: `Ec2-S3`
* Service: **EC2**
* Policy: **AWSS3FullAccess**

The first role will allow the Lambda service to read and write files from/to S3.

The second role will be assigned to EC2 machines and will similarly allow them to read and write files from/to S3.

---

# Creating the Lambda Function

Next, we will create a Lambda function that will resize the image uploaded to the first S3 bucket to **100x100** and copy the resized image to the second S3 bucket.

**5:** Go to the Lambda service and create a new function.

* Runtime: **Node.js 8.10**
* Role: `Lambda-S3`

Create the function.

Under **Code Entry Type**, select **Upload a zip file**, choose the `ImageResize.zip` file, and click **Save**.

Then, under **Trigger**, select **S3**.

* Bucket: `projeisminiz`
* Event: **All Object Create Events**

Save everything.

Finally, under **Basic Settings**, set the timeout value to **10 seconds** and save.

**6:** Upload a `.jpg` file to the `projeisminiz` bucket.

Then check whether a smaller version of the image has been created in the `projeisminiz-resized` bucket.

If it has, you have successfully created the Lambda function.

---

# Creating the Security Group

We are creating the Security Group that will be used throughout the project.

**7:** Create a Security Group named `Proje-SecGroup` that you will use until the end of the project.

Allow HTTP port **80** and SSH port **22** from `0.0.0.0`, meaning from anywhere.

---

# Creating the EC2 Template Instance

We are now creating and configuring the EC2 instance that will be used as our template.

**8:** Create a new EC2 instance.

* Instance type: **T2 Micro**
* AMI: **Amazon Linux 2**
* Subnet: One of the Public Subnets
* IAM Role: `Ec2-S3`
* Security Group: `Proje-SecGroup`

This will be your template machine.

All operations will be performed on this machine. Later, we will create an AMI from it and use that AMI to create the other machines.

---

# Installing httpd, MySQL, and PHP 7.2

We will install the `httpd`, `mysql`, and `php7.2` packages on the machine and configure the `httpd` service to start automatically whenever the machine starts. We will then verify that everything has been installed correctly.

**9:** Connect to the machine you created.

First, update all packages using the `yum` package manager.

**10:** Use the `yum` package manager to install the `httpd` and `mysql` packages.

**11:** Use the `amazon-linux-extras` package manager to install the `php7.2` package.

**12:** Configure the `httpd` service to start automatically whenever the machine starts.

**13:** Restart the `httpd` service.

**14:** Go to the `/var/www/html` directory and create a file named `test.php`.

Add the following content to the file:

```php
<?php
phpinfo();
?>
```

Save the file.

Then open a browser on your own machine and go to:

```text
http://ec2makinenin_ip_adresi/test.php
```

If the page opens and displays the PHP version information, PHP has been successfully installed on the machine.

Congratulations.

---

# Creating the EFS File System

We will use the EFS service to keep all files in a shared location. We will also configure the necessary Security Group settings to establish a connection between EC2 and EFS.

**15:** Now it is time to configure EFS.

Go to the EFS service and start creating a new file system.

* VPC: Select the correct VPC.
* Subnets: Select all Public Subnets.
* Security Group: Select the **Default Security Group**.

The created EFS File System will have a name similar to:

```text
fs-6971afa1
```

Take note of this name.

**16:** In the EC2 service, go to **Security Groups**.

Find the default Security Group of this VPC.

Under **Inbound Rules**, add a new rule:

* Service: **NFS**
* Protocol/Port: **TCP 2049**
* Source: **Custom**
* Source Security Group: `Proje-SecGroup`

This will allow EC2 machines assigned to `Proje-SecGroup` to access the EFS File System.

---

# Mounting the EFS File System

It is now time to mount the EFS File System on the machine.

**17:** Return to the virtual machine you created.

Use the `yum` package manager to install:

```text
amazon-efs-utils
```

**18:** Go to:

```text
/var/www
```

**19:** Mount the EFS File System you created to the `html` directory located under `/var/www`.

This means that all files inside the `html` directory will actually be stored on the EFS File System.

You can find the required mount command on the EFS service screen.

**20:** Switch to root and then go to the `/etc` directory.

There is a file named `fstab` in this directory.

Edit this file with a text editor and add the following to its first line.

Replace the EFS File System name at the beginning with your own EFS File System name.

This will ensure that the mount operation is performed automatically every time the machine restarts.

```text
fs-6971afa1:/ /var/www/html efs defaults,_netdev 0 0
```

**21:** Return to:

```text
/var/wwww/html
```

Create a new directory named `images` using:

```bash
mkdir images
```

Then use:

```bash
chmod 777 images
```

to give the directory the permissions required for the PHP application to save files, even though this permission level is excessive.

---

# Creating the Database

Now let's create our database.

**22:** It is time to create our RDS database.

Go to the RDS service and create a new database.

* Engine: **MySQL**
* Continue with: **Dev/Test - MySQL**
* Instance Class: **T2 Micro**
* Enable: **Create replica in different zone**

This will configure it for failover.

Enter the following information:

```text
instance identifier: projedbinstance
master username: projemaster
password: master1234
```

Select the VPC and select **No** for Public Access.

Keep **Create new VPC security group** selected so that a new Security Group will be created.

Set:

```text
Database name: proje
```

Leave the remaining settings unchanged and create the database.

Wait for the database to be created.

Then take note of the URL you will use to connect to it, which will look similar to:

```text
projedbinstance.cqub0g199gzf.eu-west-1.rds.amazonaws.com
```

**23:** Go to the **Security Groups** section under the EC2 service.

Find the Security Group that was just created for RDS.

If you do not remember its name, return to RDS and check which Security Group is being used.

Add an inbound rule:

* Port: **MySQL 3306**
* Source: **Custom**
* Source Security Group: `Proje-SecGroup`

This will allow the EC2 virtual machines to access our RDS database.

**24:** Return to your EC2 machine.

Now we will use the `mysql` command from this machine to connect to our RDS database and create a new table.

In the EC2 virtual machine shell, enter:

```bash
mysql -h projedbinstance.cqub0g199gzf.eu-west-1.rds.amazonaws.com -P 3306 -u projemaster -p
```

Of course, replace the database URL with the URL of your own system.

You will be prompted for a password.

Enter:

```text
master1234
```

Congratulations, you have connected to the RDS database.

**25:** First, switch to the `proje` database using:

```sql
USE proje;
```

**26:** Enter the following command to create a table named `visitors`:

```sql
CREATE TABLE visitors (name VARCHAR(30), email VARCHAR(30), phone VARCHAR(30), photo VARCHAR(30));
```

**27:** Run:

```sql
show Tables;
```

to verify that the table was created.

Then use:

```text
exit
```

to terminate the MySQL connection and return to the EC2 shell.

---

# Creating the Application

It is now time to create our application.

**28:** Return to your computer and go to the:

```text
Proje/Php Uygulama
```

directory.

Open the `add.php` file with a text editor.

Find:

```php
$servername = "projedbinstance.cqub0g199gzf.eu-west-1.rds.amazonaws.com";
```

Replace the value of this variable with the address of your own RDS database and save the file.

**29:** Open the `view.php` file in the same directory.

Find:

```php
$servername = "projedbinstance.cqub0g199gzf.eu-west-1.rds.amazonaws.com";
```

Replace the value of this variable with the address of your own RDS database and save the file.

Additionally, replace:

```php
Echo "<img src=https://s3-eu-west-1.amazonaws.com/projeisminiz-resized/resized-images/"
```

with the address of your own S3 bucket.

---

# Copying the Application Files to the EC2 Template Machine

**30:** Go to the EC2 template machine.

First, create a file named:

```text
index.html
```

Copy the contents of the `index.html` file located in:

```text
Proje/Php Uygulama
```

into this file and save it.

**31:** Similarly, create a file named:

```text
add.php
```

Copy the contents of the `index.html` file located in:

```text
Proje/Php Uygulama
```

into this file and save it.

**32:** Similarly, create a file named:

```text
view.php
```

Copy the contents of the `index.html` file located in:

```text
Proje/Php Uygulama
```

into this file and save it.

---

# Creating the S3 Synchronization Script

At this point, we are creating a new script.

This script will copy all images uploaded to the `images` directory to our S3 bucket. This will trigger the Lambda function, which will create a smaller copy of the image.

**33:** In the same directory, create a file named:

```text
s3.sh
```

Modify the following content as necessary, paste it into the file, and save it:

```bash
#!/bin/bash
aws s3 sync /var/www/html/images s3://projeisminiz/images
```

---

# Configuring Crontab

We will use Crontab to make this script run every 2 minutes.

**34:** Open crontab using:

```bash
sudo crontab -e
```

Press the `i` key to enter edit mode.

Enter:

```text
*/2 * * * * /var/www/html/s3.sh
```

Then exit using:

```text
:x
```

---

# Testing the Application

**35:** It is now time to verify whether our application is working.

Open a browser on your own machine and go to:

```text
http://ec2makinenin_ip_adresi/
```

A form page should appear.

Enter:

* Name
* Email
* Phone

Also select a `.jpg` image and click the **ADD** button.

If you see the following message:

```text
Kayit basariyla yaratildi Dosya basarili bir sekilde sunucuya yuklendi ve diger kayitlar da veritabanına basarili sekilde girildi
```

then congratulations, everything is working correctly.

Wait approximately **2 minutes** for the image to synchronize.

Afterward, click the **Kayitlari gör** button.

If you can see the record you entered and its image, the process is complete.

---

# Creating the AMI

We have completed the installations. We can now create a new AMI from this machine.

**36:** Everything is ready.

Now **Stop** the EC2 template machine — **be careful, do not terminate it**.

Then go to **EBS → Volumes** and create a Snapshot of the main disk attached to this machine.

**37:** Go to **EBS → Snapshots**.

Create an AMI from the Snapshot you just created.

Set:

```text
Name: ProjeAMI
Virtualization Type: hardware-assisted virtualization
```

Leave the remaining options at their defaults.

---

# Creating the Load Balancer

We are creating a Load Balancer so that we can distribute traffic among the machines we will create shortly.

**38:** Go to **Load Balancing → Target Groups** and create a Target Group.

* VPC: Select the appropriate VPC.
* Path: `/index.html`

Leave the remaining settings at their defaults.

**39:** Go to **Load Balancing → Load Balancers** and create a new **Application Load Balancer**.

Configure it as follows:

* Scheme: **Internet-facing**
* Subnets: Select the Public Subnets in our VPC.
* Security Group: `Proje-SecGroup`
* Target Group: Select the Target Group we just created.

Create the Load Balancer.

At this stage, no targets will be registered because there are no machines yet.

This is normal.

---

# Creating the Auto Scaling Group

We will create new machines using the AMI we created. We will use Auto Scaling for this.

**40:** Go to **Auto Scaling → Launch Configuration** and create a new Launch Configuration.

* AMI: `ProjeAMI`
* Instance Type: **T2 Micro**
* IAM Role: `Ec2-S3`
* Security Group: `Proje-SecGroup`

Complete the process.

**41:** Go to **Auto Scaling → Auto Scaling Groups** and create a new Auto Scaling Group.

* Launch Configuration: Select the Launch Configuration created previously.
* Instance Size: **3 instances**
* VPC: Select the appropriate VPC.
* Subnets: Add all Public Subnets.

Under **Advanced Details**, select:

> **Receive traffic from one or more load balancers**

Immediately afterward, under **Target Group**, select the Target Group created previously.

Proceed to the next screen.

Select:

> **Use scaling policies to adjust the capacity of this group**

Configure it so that the group can scale between **3 and 5 instances**.

In the **Average CPU Utilization** field below, enter:

```text
90
```

Continue until the final screen and complete the setup.

**42:** Return to the EC2 Dashboard and verify that **3 machines** have been created.

**43:** Wait **5 minutes**.

Then go to **Load Balancing → Target Groups**, select your Target Group, and go to the **Targets** tab.

Verify that the created EC2 machines are **healthy**.

**44:** Go to **Load Balancing → Load Balancers**.

Copy the DNS address of the Load Balancer you created.

Open a browser on your own machine and enter this address.

Verify that your web application is working.

---

# Creating the CloudFront Distribution

We are creating a CloudFront distribution so that our application can be accessed quickly from anywhere in the world.

**45:** Go to CloudFront and create a web distribution.

Configure it as follows:

* **Origin Domain Name:** Select your Load Balancer address.
* **Alternate Domain Names:** `www.dnsadresiniz.com`
* **Price Class:** EU, US and Canada

Leave the remaining settings at their defaults.

Create the CloudFront distribution.

Wait **10 minutes** before proceeding to the next step.

---

# Creating the Route 53 Record

As the final step, we will use Route 53 to create a `www` record through which the application can be accessed.

**46:** Go to the Route 53 service.

Find your **Hosted Zone**.

Create a new **A record**.

Configure it as follows:

* **Name:** `www`
* **Alias:** Yes
* **Alias Target:** Select the address of your CloudFront distribution.

**47:** Go to:

```text
www.dnsadresiniz.com
```

and verify that your web application is working.

