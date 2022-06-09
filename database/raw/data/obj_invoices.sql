/*
SQLyog Ultimate v12.08 (64 bit)
MySQL - 10.4.13-MariaDB : Database - db_autoinvoice
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`db_autoinvoice` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `db_autoinvoice`;

/*Data for the table `invoices` */

insert  into `invoices`(`id`,`created_by`,`hash`,`name`,`status`,`schedule_day`,`schedule_time`,`frequency`,`invoice_no`,`created_at`,`updated_at`,`contact_details`,`bill_to_details`,`account_details`) values (1,1,'mHt8fbMEloISVahO','example 1.3','inactive','wednesday','15:15','monthly',1,'2022-05-31 06:15:52','2022-06-09 08:03:47','',NULL,NULL),(2,1,'lWnpGb7KaOotTamj','example 2','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 06:15:56','2022-05-31 06:15:56',NULL,NULL,NULL),(3,1,'vlIRzmqQ0RAN3ctG','example 3.1','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 06:15:57','2022-06-08 00:53:26',NULL,NULL,NULL),(4,1,'0sjhjtfjsxXDtTTI','example 4.1','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 06:15:58','2022-06-07 08:50:50',NULL,NULL,NULL),(5,1,'4Lzy0SECK0mYK05P','example 5','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 06:15:58','2022-05-31 06:15:58',NULL,NULL,NULL),(6,1,'LIRBpjYuk0mwnypF','example 6.1.1','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 06:15:59','2022-06-08 00:53:55',NULL,NULL,NULL),(7,1,'dWUgjt17OZ8H1Q2C','example 7','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 06:15:59','2022-06-07 08:53:34',NULL,NULL,NULL),(8,1,'kjq4ldea2n0xvywR','example 8.1','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 06:16:00','2022-06-06 09:11:01',NULL,NULL,NULL),(9,1,'xwPwtxHx8UIwCf3v','example 9.1','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 06:17:00','2022-06-02 05:59:50',NULL,NULL,NULL),(10,1,'PRVQflOR2E','example 10','inactive','tuesday','13:00','bi-monthly',1,'2022-05-31 06:17:47','2022-06-09 08:03:42',NULL,NULL,NULL),(11,1,'w8gda6FOUb','example 11.2','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 06:17:48','2022-06-08 09:17:24',NULL,NULL,NULL),(12,1,'ZCXuIUjrfy86XH','example 12','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 06:17:54','2022-05-31 06:17:54',NULL,NULL,NULL),(13,1,'zNkggqjr07Y7109G','example 13.1','inactive','wednesday','13:00','bi-monthly',1,'2022-05-31 07:00:36','2022-06-06 09:15:03',NULL,NULL,NULL),(15,1,'lqUJh1Wdw60c','obsidian 2','inactive','wednesday','09:00','bi-monthly',1,'2022-06-02 08:53:51','2022-06-06 06:55:42',NULL,NULL,NULL),(16,1,'tTJJuLXRq0MA','obsidian 1.1','active','wednesday','09:00','bi-monthly',1,'2022-06-02 08:53:59','2022-06-09 09:00:36','{\n\"address\": \"#1 baranggay, baguio\",\n\"phone\": \"0999-123-4567\",\n\"email\": \"someone@example.com\"\n}','{\n\"name\": \"plummet and fun co.\",\n\"address\": \"baranggay, baguio\",\n\"email\": \"finance@example.com\"\n}','{\n\"name\":\"obsidian\",\n\"account_number\":\"000-111-222-333\",\n\"bank\":\"beef co.\"\n}'),(21,1,'8ZC4Jh5yff','exarkun 12.1','inactive','wednesday','10:00','monthly',1,'2022-06-03 04:32:15','2022-06-08 09:16:23',NULL,NULL,NULL),(22,1,'OayurnKE','navarra 1.0.1','inactive','wednesday','11:15','monthly',1,'2022-06-03 07:09:01','2022-06-09 05:38:27',NULL,NULL,NULL),(24,1,'99HFZ77oW0w6','bombardier','inactive','wednesday','09:00','bi-monthly',1,'2022-06-09 05:39:26','2022-06-09 08:03:29',NULL,NULL,NULL),(25,1,'tnTO5WVV','monditis','inactive','monday','09:00','bi-monthly',1,'2022-06-09 06:14:10','2022-06-09 06:24:55',NULL,NULL,NULL),(26,1,'XuV7oHHlLZ','tuesditis','inactive','tuesday','09:00','bi-monthly',1,'2022-06-09 06:16:27','2022-06-09 06:25:01',NULL,NULL,NULL),(27,1,'clXt5itYRuRlKrl','friyay','inactive','friday','18:05','monthly',1,'2022-06-09 06:23:43','2022-06-09 08:03:18',NULL,NULL,NULL),(28,1,'CDxgDnyy','temporal','inactive','thursday','09:00','bi-monthly',1,'2022-06-09 08:01:10','2022-06-09 08:03:11',NULL,NULL,NULL),(29,1,'mF9iTyoxEIb6JR','obsidian 3','inactive','wednesday','09:00','bi-monthly',1,'2022-06-09 08:56:43','2022-06-09 09:01:54',NULL,NULL,NULL),(30,1,'TRrLO3hOb0V','obsidian 4','inactive','wednesday','09:00','bi-monthly',1,'2022-06-09 08:59:22','2022-06-09 09:01:48',NULL,NULL,NULL),(31,1,'0xnPlzxftE7jxo33','beef','inactive','monday','09:00','bi-monthly',1,'2022-06-09 09:02:18','2022-06-09 09:02:31',NULL,NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
