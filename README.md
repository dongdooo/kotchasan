# คชสาร เว็บเฟรมเวิร์ค (Kotchasan Web Framework)
ช้างนอกจากจะเป็นสัญลักษณ์ของ PHP แล้ว ยังเป็นสัญลักษณ์ประจำชาติของเราอีก
ผมเลยเลือกที่จะใช้ชื่อนี้เป็นชื่อของ Framework ที่ออกแบบโดยคนไทย 100%

##คุณสมบัติ
* สถาปัตยกรรม MMVC (Modules Model View Controller) ทำให้การเพิ่มหรือลดโมดูลเป็นไปโดยง่าย ไม่ขึ้นแก่กัน
* สนับสนุนการทำงานแบบหลายโปรเจ็ค
* ปฏิบัติตามมาตรฐาน PSR-1, PSR-2, PSR-3, PSR-4, PSR-6, PSR-7
* เป็น PHP Framework ที่ได้รับการ Optimize ทั้งทางด้านความเร็วและประสิทธิภาพในการทำงาน
ตลอดจนการใช้หน่วยความจำ ให้มีประสิทธิภาพดีที่สุด ทำให้สามารถทำงานได้เร็วกว่า และรองรับผู้เยี่ยมชมพร้อมกันได้มากกว่า

## องค์ประกอบของ คชสาร
คชสารจะประกอบด้วยเฟรมเวิร์คหลัก 3 ตัว ที่ออกแบบเพื่อใช้ร่วมกัน ทั้งส่วนของ PHP, CSS และ Javascript
* Kotchasan PHP Framework
* GCSS CSS Framework
* GAjax Javascript Framework

## ความต้องการ
* PHP 5.3 ขึ้นไป
* ext-mbstring
* PDO Mysql

## การติดตั้งและนำไปใช้งาน
ผมออกแบบคชสารเพื่อหลีกเลี่ยงการติดตั้งที่ยุ่งยากตามแบบของ PHP Framework ทั่วไป
โดยสามารถดาวน์โหลด source code ทั้งหมดจาก github ไปใช้งานได้ทันทีโดยไม่ต้องติดตั้งหรือตั้งค่าใดๆ
หรือสามารถติดตั้งผ่าน Composer ได้ ```composer require goragod/kotchasan``` https://packagist.org/packages/goragod/kotchasan

##เงื่อนไขการใช้งาน
* เป็น Open Source สามารถนำไปใช้งานได้ฟรี ไม่มีเงื่อนไข
* สามารถนำไปพัฒนาต่อยอดเป็นลิขสิทธิ์ของตัวเองได้ (เฉพาะส่วนที่พัฒนาเพิ่มเติมเอง)

##ตัวอย่าง
โค้ดตัวอย่างทั้งหมดอยู่ในโฟลเดอร์ projects/ ถ้าต้องการทดสอบลองเรียกได้ในนั้น
ส่วนโปรเจ็ค recordset มีการเรียกใช้ฐานข้อมูลร่วมด้วย ต้องกำหนดค่าฐานข้อมูลที่ settings/database.php ให้ถูกต้องก่อน
และต้องสร้างตารางฐานข้อมูลด้วย ตามใน projects/orm/modules/index/models/world.php

* http://www.kotchasan.com/projects/welcome/ หน้าต้อนรับของคชสาร
* http://www.kotchasan.com/projects/site/ สร้างเว็บไซต์ด้วย template และมีเมนู แบบง่ายๆ
* http://www.kotchasan.com/projects/recordset/ ตัวอย่างการใช้งานฐานข้อมูล (Recordset)
* http://www.kotchasan.com/projects/admin/ ตัวอย่างการใช้งานฟอร์ม Login
* http://www.kotchasan.com/projects/youtube/ ตัวอย่างการใช้งาน Youtube API
* http://www.kotchasan.com/projects/pdf/ ตัวอย่างการแปลง HTML เป็น PDF