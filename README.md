Dodan modul za Hrvatsku fiskalizaciju. Nakon instalacije originala (v 1.4.10) potrebno je napraviti nekoliko koraka da bi fiskalizacija radila: 


1. Postavke-> Prilagođena polja -> Tablica Korisnik dodati (samo ono pisano velikim slovima):
  1.1 IBAN 
  *1.2 OIB KORISNIKA (*oib blagajnika)
  **1.3 SWIFT (**nije nužno)

2. Postavke -> Grupe računa -> Fiskalni račun (definiran internim aktom) u formatu:
  redni_broj_računa/oznaka_poslovnog_prostora/broj_naplatnog_uređaja  - bitno je odvajanje sa "/"

3. Postavke -> Porezne stope. Dodati porezne stope (5,15,25) u formatu: Naziv porezne stope: PDV (5), Porezna stopa(%): 5. Naziv mora sadržavati velikim slovima PDV.

4. Postavke -> Računi -> Zadani pdf predložak  -> racun

5. U tablicu ip_settings dodati sljedeće vrijednosti:
<<<<<<< HEAD
    5.1. setting_key - aplikativni_cert_path, setting_value - putanja_do_aplikativnog_certifikata (obavezno P12, obicno FISKAL1_P12)
    5.2. setting_key: aplikativni_cert_password, setting_value: zaporka certifikata
    5.3. setting_key: ca_cert_path, setting_value: putanja do FIna root certifikata (u cer formatu)
    5.4. setting_key: sustav_pdv, setting_value: 1 (za u sustavu PDV-a) ili 0 (za nije u sustavu PDV-a)
    5.5. setting_key: oznaka_slijednosti, setting_value: P (za nivou poslovnog prostora) ili N (za na nivou naplatnog uređaja)
>>>>>>> 

6. Dodati nova polja u tabli ip_invoices:

ALTER TABLE `ip_invoices` ADD `invoice_uuid` VARCHAR(36) NOT NULL AFTER `creditinvoice_parent_id`, ADD `invoice_jir` VARCHAR(36) NOT NULL AFTER `invoice_uuid`, ADD `invoice_zki` VARCHAR(36) NOT NULL AFTER `invoice_jir`, ADD `invoice_naknadna_dostava` TINYINT NOT NULL DEFAULT '0' AFTER `invoice_zki`, ADD `invoice_fiskalizirano` TINYINT NOT NULL DEFAULT '0' AFTER `invoice_naknadna_dostava`, ADD `invoice_greska` VARCHAR(1000) NOT NULL AFTER `invoice_fiskalizirano`, ADD `invoice_datum_slanja` DATETIME NOT NULL AFTER `invoice_greska`;

7. Postavke -> Korsincki računi -> Korisnik -> Porezne informacije OIB -> Oib poslovnog subjekta

8. Kopirati fileove iz InvoicePlaneFiskal u instalacijski direktorij


*** TODO: Poruka prostora, U izvještajima raščlanit poreze po stopi, dodat porez na potrošnju (NE RADI), ne radu popusti iz ponude (ne koristiti). ***  


![InvoicePlane](http://invoiceplane.com/content/logo/PNG/logo_300x150.png)
#### _Version 1.4.10_

InvoicePlane is a self-hosted open source application for managing your invoices, clients and payments.    
For more information visit __[InvoicePlane.com](https://invoiceplane.com)__ or try the __[demo](https://demo.invoiceplane.com)__.

### Quick Installation

1. Download the [latest version](https://invoiceplane.com/downloads)
2. Extract the package and copy all files to your webserver / webspace.
3. Set your URL in the `index.php` file.
4. Open `http://your-invoiceplane-domain.com/index.php/setup` and follow the instructions.

#### Remove `index.php` from the URL

1. Make sure that [mod_rewrite](https://go.invoiceplane.com/apachemodrewrite) is enabled on your web server.
2. Remove `index.php` from `$config['index_page'] = 'index.php';` in the file `/application/config/config.php`
3. Rename the `htaccess` file to `.htaccess`

If you want to install InvoicePlane in a subfolder (e.g. `http://your-invoiceplane-domain.com/invoices/`) you have to change the .htaccess file. The instructions can be found within the file.

### Support / Development / Chat

[![Wiki](https://img.shields.io/badge/Help%3A-Official%20Wiki-429ae1.svg)](https://wiki.invoiceplane.com/)    
[![Community Forums](https://img.shields.io/badge/Help%3A-Community%20Forums-429ae1.svg)](https://community.invoiceplane.com/)    
[![Issue Tracker](https://img.shields.io/badge/Development%3A-Issue%20Tracker-429ae1.svg)](https://development.invoiceplane.com/)    
[![Roadmap](https://img.shields.io/badge/Development%3A-Roadmap-429ae1.svg)](https://go.invoiceplane.com/roadmapv1) 

---

### Security Vulnerabilities

If you discover a security vulnerability please send an e-mail to mail@invoiceplane.com before disclosing the vulnerability to the public!
All security vulnerabilities will be promptly addressed.

---
  
*The name 'InvoicePlane' and the InvoicePlane logo are both copyright by Kovah.de and InvoicePlane.com
and their usage is restricted! For more information visit invoiceplane.com/license-copyright*
