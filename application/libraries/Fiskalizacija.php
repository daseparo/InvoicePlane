<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


/*
 * Fiskalizacija za InvoicePlane ver 1.4.10
 * 
 * Library za Hrvatsku fiskalizaciju 
 * Autor: Dario šeparović
 * Rađeno prema primjeru : https://github.com/nticaric/fiskalizacija


*/


class Fiskalizacija
{
    var $invoice;
    var $doc;
    var $root; 
    var $UriId;
    var $jir_ok;
    public function Fiskalizacija($params)
    {
        $CI = &get_instance();
        $this->invoice = $params['invoice'];
        $this->invoice_items = $params['invoice_items'];
        $this->aplikativni_cert_password = $CI->mdl_settings->setting('aplikativni_cert_password');
        $this->aplikativni_cert_path = $CI->mdl_settings->setting('aplikativni_cert_path');
        $this->ca_cert_path = $CI->mdl_settings->setting('ca_cert_path');
        $this->sustav_pdv = $CI->mdl_settings->setting('sustav_pdv');
        $this->oznaka_slijednosti = $CI->mdl_settings->setting('oznaka_slijednosti');
        $this->cis = $CI->mdl_settings->setting('cis');


    }

    public function guidv4($data)
    {
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

   
    public function get_zki()
    {
       $oib_poduzeca = $this->invoice->user_vat_id;
       $broj_racuna = $this->invoice->invoice_number;
       $broj_racuna_clean = str_replace('/','',$broj_racuna);
       $datum_izdavanja =  date("d.m.Y", strtotime($this->invoice->invoice_date_created)); 
       $datum_vrijeme_izdavanja_racuna = '';
       $datum_vrijeme_izdavanja_racuna .=$datum_izdavanja;
       $datum_vrijeme_izdavanja_racuna .= ' ';
       $datum_vrijeme_izdavanja_racuna .= $this->invoice->invoice_time_created;
       $zki_unsigned ='';
       $zki_unsigned .= $oib_poduzeca;
       $zki_unsigned .= $datum_vrijeme_izdavanja_racuna;
       $zki_unsigned .= $broj_racuna_clean;
       $zki_unsigned .= $this->invoice->invoice_total;
       
       $certificate = $this->handle_aplikativni_certifikat();
        
       $publicCertificate = $certificate['cert'];
       $privateKey = $certificate['pkey'];
       
       $privateKeyResource = openssl_pkey_get_private($privateKey, $this->aplikativni_cert_password);
       $publicCertificateData = openssl_x509_parse($publicCertificate);
       
       $zki_signed = null;

       openssl_sign($zki_unsigned, $zki_signed, $privateKeyResource, OPENSSL_ALGO_SHA1);
       
       $ZastKod = md5($zki_signed); 
       $zki = $ZastKod;       
        
      return $zki;
    } 
    public function get_jir($XMLRequestType,$uuid,$zki,$datum_slanja)
    {
        $this->handle_fina_certifikat();
        $xml_request = $this->build_xml($XMLRequestType,$uuid,$zki,$datum_slanja);
        $xml_request_signed = $this->sign_xml($xml_request,$XMLRequestType);
        $response = $this->execute_curl_request($xml_request_signed);
       
       return $response;
    }

    
    public function handle_aplikativni_certifikat()
    {
        $certificate = null;
        
        if (openssl_pkcs12_read(file_get_contents($this->aplikativni_cert_path),$certificate,$this->aplikativni_cert_password)) {
            
            return $certificate;
        
                    
        } else {
              
               echo "GREŠKA: Nije moguće pročitati certifikat. Provjerite putanju i zaporku certifikata u bazu, provjerite tip (pkcs12) i ekstenziju certifikata (.pfx). Trenutna putanja: $this->aplikativni_cert_path ";
               exit;

            
        }

                
      }

    public function handle_fina_certifikat()
    {
    $certificateCAcer = $this->ca_cert_path;
    $certificateCAcerContent = file_get_contents($certificateCAcer);
    /* Convert .cer to .pem, cURL uses .pem */
    $certificateCApemContent =  '-----BEGIN CERTIFICATE-----'.PHP_EOL
           .chunk_split(base64_encode($certificateCAcerContent), 64, PHP_EOL)
              .'-----END CERTIFICATE-----'.PHP_EOL;
    $certificateCApem = $certificateCAcer.'.pem';
    
    file_put_contents($certificateCApem, $certificateCApemContent); 

    return $certificateCApem;
    }

    public function payment_method()
    {
        switch ($this->invoice->payment_method){
        case "1":
            return "G";
        case "2":
            return "O";
        case "3":
            return "K";
        case "4":
            return "T";
        case "5":
            return "O";
        case "6":
            return "C";


        }
    }
    public function naknadna_dostava()
    {
        if ($this->invoice->invoice_naknadna_dostava == 1)
        { 
            return "true";
        }
        else 
        {
            return "false";
        }
    }

    public function build_xml($XMLRequestType,$uuid,$zki,$datum_slanja)

    {
    
        $this->UriId = uniqid();
    
    $datum_slanje = date("d.m.Y h:i:s", strtotime($datum_slanja));
        $formatirano_vrijeme =str_replace(' ','T',$datum_slanje);

    if ($XMLRequestType == 'RacunZahtjev') {
   
    $ns = 'tns';
    $writer = new XMLWriter();
    $writer->openMemory();

    
    $writer->startDocument('1.0', 'UTF-8');
    $writer->setIndent(4);
    $writer->startElementNs($ns, 'RacunZahtjev', 'http://www.apis-it.hr/fin/2012/types/f73');
    $writer->writeAttribute('Id', $this->UriId);

    $writer->startElementNs($ns, 'Zaglavlje', null);
    $writer->writeElementNs($ns, 'IdPoruke', null, $uuid);
    $writer->writeElementNs($ns, 'DatumVrijeme', null, $formatirano_vrijeme);
    $writer->endElement(); /* #Zaglavlje */
    
    $writer->startElementNs($ns, 'Racun', null);
    $writer->writeElementNs($ns, 'Oib', null, $this->invoice->user_vat_id);
    $writer->writeElementNs($ns, 'USustPdv', null, $this->sustav_pdv);
    $writer->writeElementNs($ns, 'DatVrijeme', null,date("d.m.Y", strtotime($this->invoice->invoice_date_created)).'T'. $this->invoice->invoice_time_created );
    $writer->writeElementNs($ns, 'OznSlijed', null, $this->oznaka_slijednosti); /* P ili N => P na nivou Poslovnog prostora, N na nivou naplatnog uredaja */
    
    list ($brojcanaOznakaRacuna,$oznakaPoslovnogProstora, $oznakaNaplatnogUredaja) = explode ("/", $this->invoice->invoice_number);

    $writer->startElementNs($ns, 'BrRac', null);
    $writer->writeElementNs($ns, 'BrOznRac', null, $brojcanaOznakaRacuna);
    $writer->writeElementNs($ns, 'OznPosPr', null, $oznakaPoslovnogProstora);
    $writer->writeElementNs($ns, 'OznNapUr', null, $oznakaNaplatnogUredaja);
    $writer->endElement(); /* #BrRac */
    
    $lista_poreza = array ("0"=>array("stopa" => 0,"osnovica"=>0,"iznos_poreza"=> 0)) ;
    $imapdv=false;
    foreach ($this->invoice_items as $stavka)
    {
        if (strpos($stavka->item_tax_rate_name, 'PDV') !== false)
        {
            $imapdv=true;
            if (array_search($stavka->item_tax_rate_percent,array_column($lista_poreza,"stopa")) == false )
            {
                array_push($lista_poreza,array("stopa"=>$stavka->item_tax_rate_percent,"osnovica"=>$stavka->item_subtotal,"iznos_poreza" => $stavka->item_tax_total)); 
            }   
            else
            {
                foreach ($lista_poreza as &$porez)
                {        
                   if ($porez["stopa"] == $stavka->item_tax_rate_percent)
                   {   
                       $porez["osnovica"] = $porez["osnovica"]+ $stavka->item_subtotal;
                       $porez["iznos_poreza"] =$porez["iznos_poreza"] + $stavka->item_tax_total;
                  }

                }
                unset($porez);  

            }        
        }
            
    }
    if ($imapdv)
    {
  $writer->startElementNs($ns, 'Pdv', null);
    foreach ($lista_poreza as $porez1)
    {  
        
        if ($porez1["stopa"] != 0)
        {       
           
          
            $writer->startElementNs($ns, 'Porez', null);
            $writer->writeElementNs($ns, 'Stopa', null, $porez1["stopa"]);
            $writer->writeElementNs($ns, 'Osnovica', null,number_format($porez1["osnovica"],2,'.',''));
            $writer->writeElementNs($ns, 'Iznos', null, number_format($porez1["iznos_poreza"],2,'.',''));
            $writer->endElement(); /* #Porez */ 
           
        
        }
    }
    $writer->endElement(); /* #Pdv */
    }
    
    $writer->writeElementNs($ns, 'IznosUkupno', null, $this->invoice->invoice_total);
    $writer->writeElementNs($ns, 'NacinPlac', null, $this->payment_method());
    $writer->writeElementNs($ns, 'OibOper', null, $this->invoice->user_custom_oib_korisnika);
    $writer->writeElementNs($ns, 'ZastKod', null, $zki);
    $writer->writeElementNs($ns, 'NakDost', null, $this->naknadna_dostava());
    
    $writer->endElement(); /* #Racun */
    
    $writer->endElement(); /* #RacunZahtjev */
    
    $writer->endDocument();
        
    $XMLRequest = $writer->outputMemory();
    
    return $XMLRequest;
    }
    }
    
    public function sign_xml($xml_request,$XMLRequestType)
    {

    $XMLRequestDOMDoc = new DOMDocument();
    $XMLRequestDOMDoc->loadXML($xml_request);
    
    $canonical = $XMLRequestDOMDoc->C14N();
    $DigestValue = base64_encode(hash('sha1', $canonical, true));

    $rootElem = $XMLRequestDOMDoc->documentElement;
    
    $SignatureNode = $rootElem->appendChild(new DOMElement('Signature'));
    $SignatureNode->setAttribute('xmlns','http://www.w3.org/2000/09/xmldsig#');
    
    $SignedInfoNode = $SignatureNode->appendChild(new DOMElement('SignedInfo'));
    $SignedInfoNode->setAttribute('xmlns','http://www.w3.org/2000/09/xmldsig#');
    
    $CanonicalizationMethodNode = $SignedInfoNode->appendChild(new DOMElement('CanonicalizationMethod'));
    $CanonicalizationMethodNode->setAttribute('Algorithm','http://www.w3.org/2001/10/xml-exc-c14n#');
    
    $SignatureMethodNode = $SignedInfoNode->appendChild(new DOMElement('SignatureMethod'));
    $SignatureMethodNode->setAttribute('Algorithm','http://www.w3.org/2000/09/xmldsig#rsa-sha1');

    $ReferenceNode = $SignedInfoNode->appendChild(new DOMElement('Reference'));
    $ReferenceNode->setAttribute('URI', sprintf('#%s', $this->UriId));
    
    $TransformsNode = $ReferenceNode->appendChild(new DOMElement('Transforms'));
    $Transform1Node = $TransformsNode->appendChild(new DOMElement('Transform'));
    
    $Transform1Node->setAttribute('Algorithm','http://www.w3.org/2000/09/xmldsig#enveloped-signature');
    $Transform2Node = $TransformsNode->appendChild(new DOMElement('Transform'));
    $Transform2Node->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');
    
    $DigestMethodNode = $ReferenceNode->appendChild(new DOMElement('DigestMethod'));
    $DigestMethodNode->setAttribute('Algorithm','http://www.w3.org/2000/09/xmldsig#sha1');
    
    $ReferenceNode->appendChild(new DOMElement('DigestValue', $DigestValue));
    $SignedInfoNode = $XMLRequestDOMDoc->getElementsByTagName('SignedInfo')->item(0);

    $certificate = $this->handle_aplikativni_certifikat();
    
    $publicCertificate = $certificate['cert'];
    $privateKey = $certificate['pkey'];
    $privateKeyResource = openssl_pkey_get_private($privateKey, $this->aplikativni_cert_password);
    $publicCertificateData = openssl_x509_parse($publicCertificate);

    $X509Issuer = $publicCertificateData['issuer'];
    $X509IssuerName = sprintf('OU=%s,O=%s,C=%s', $X509Issuer['OU'], $X509Issuer['O'], $X509Issuer['C']);
    
    $X509IssuerSerial = $publicCertificateData['serialNumber'];
    $publicCertificatePureString = str_replace('-----BEGIN CERTIFICATE-----', '', $publicCertificate);
    $publicCertificatePureString = str_replace('-----END CERTIFICATE-----', '', $publicCertificatePureString);
    $SignedInfoSignature = null;
    
    if (!openssl_sign($SignedInfoNode->C14N(true), $SignedInfoSignature, $privateKeyResource, OPENSSL_ALGO_SHA1)) {
            throw new Exception('Unable to sign the request');
    }
    $SignatureNode = $XMLRequestDOMDoc->getElementsByTagName('Signature')->item(0);
    $SignatureValueNode = new DOMElement('SignatureValue', base64_encode($SignedInfoSignature));
    
    $SignatureNode->appendChild($SignatureValueNode);
    $KeyInfoNode = $SignatureNode->appendChild(new DOMElement('KeyInfo'));
    $X509DataNode = $KeyInfoNode->appendChild(new DOMElement('X509Data'));
    
    $X509CertificateNode = new DOMElement('X509Certificate', $publicCertificatePureString);
    $X509DataNode->appendChild($X509CertificateNode);
    $X509IssuerSerialNode = $X509DataNode->appendChild(new DOMElement('X509IssuerSerial'));
    $X509IssuerNameNode = new DOMElement('X509IssuerName',$X509IssuerName);
    $X509IssuerSerialNode->appendChild($X509IssuerNameNode);
    $X509SerialNumberNode = new DOMElement('X509SerialNumber',$X509IssuerSerial);
    $X509IssuerSerialNode->appendChild($X509SerialNumberNode);

    //Add soap Evelope
    $envelope = new DOMDocument();
    $envelope->loadXML('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body></soapenv:Body>
            </soapenv:Envelope>');
    $envelope->encoding = 'UTF-8';
    $envelope->version = '1.0';
    $XMLRequestTypeNode = $XMLRequestDOMDoc->getElementsByTagName($XMLRequestType)->item(0);
    $XMLRequestTypeNode = $envelope->importNode($XMLRequestTypeNode, true);
    $envelope->getElementsByTagName('Body')->item(0)->appendChild($XMLRequestTypeNode);
    /* Final, signed XML request */
    $payload = $envelope->saveXML();



    // echo '<pre>'; print_r($xml_request); echo '</pre>' ; 
     
  // echo "";
 //  exit;
     

        return $payload;
    }

    public function execute_curl_request($payload)
    {
        $certificateCApem = $this->handle_fina_certifikat();
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $this->cis,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_CAINFO => $certificateCApem,
        );
        
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        
//      echo '<pre>'; print_r($response); echo '</pre>' ; 
       
           // echo "";
 //       exit;       
        if ($response) {
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $DOMResponse = new DOMDocument();
            $DOMResponse->loadXML($response);
        if ($code === 200) {
                $Jir = $DOMResponse->getElementsByTagName('Jir')->item(0);
                    if ($Jir) {
                        $jir= $Jir->nodeValue;
                        curl_close($ch);
                        $resp = array("jir_ok" => "true", "jir"=>$jir);
                        return $resp;
                               }
                        
                        curl_close($ch);
                $resp = array("jir_ok" => "false", "jir"=>"Greska");
                    
                         return $resp;
                 

                            }    
        else {
                $SifraGreske = $DOMResponse->getElementsByTagName('SifraGreske')->item(0);
                $PorukaGreske = $DOMResponse->getElementsByTagName('PorukaGreske')->item(0);
                 if ($SifraGreske && $PorukaGreske) {
                     
                     
                         curl_close($ch);
                       $resp = array("jir_ok" => "false", "jir"=>$SifraGreske->nodeValue.' '.$PorukaGreske->nodeValue);
                         

                         return $resp;
                     

                 } else {

                     curl_close($ch);
                     $resp = array ("jir_ok" => "false", "jir"=> sprintf('HTTP response code %s not suited for further actions.', $code));
                     return $resp;

                 }
             }
        } else 
        
                 {
                     curl_close($ch);

              $resp= array ("jir_ok"=>"false", "jir"=> "Nije moguće ostvariti komunikaciju prema poreznoj upravi"); 
             return $resp;

        }
       
    }

}
