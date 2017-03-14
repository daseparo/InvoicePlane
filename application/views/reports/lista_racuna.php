<html lang="<?php echo trans('cldr'); ?>">
<head>
     <meta charset="utf-8">

    <title><?php echo 'Lista izdatih računa' ?></title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/default/css/templates.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/default/css/custom-report-pdf.css">

</head>

<body>

<header class="clearfix">

    <div id="logo">
        <?php echo invoice_logo_pdf(); ?>
    </div>


    <div id="company">
        <div><b><?php echo $results[0]->user_company; ?></b></div>
        <?php if ($results[0]->user_vat_id) {
            echo '<div>' . trans('vat_id_short') . ': ' . $results[0]->user_vat_id . '</div>';
        }
        if ($results[0]->user_tax_code) {
            echo '<div>' . trans('tax_code_short') . ': ' . $results[0]->user_tax_code . '</div>';
        }
        if ($results[0]->user_address_1) {
            echo '<div>' . $results[0]->user_address_1 . '</div>';
        }
        if ($results[0]->user_address_2) {
            echo '<div>' . $results[0]->user_address_2 . '</div>';
        }
        if ($results[0]->user_city && $results[0]->user_zip) {
            echo '<div>' . $results[0]->user_city . ', ' . $results[0]->user_zip . '</div>';
        } else {
            if ($results[0]->user_city) {
                echo '<div>' . $results[0]->user_city . '</div>';
            }
            if ($results[0]->user_zip) {
                echo '<div>' . $results[0]->user_zip . '</div>';
            }
        }


                ?>
                        </div>
</header>
<?php   if ($results)
        {
        list($rbr,$oznakaPoslovnogProstora, $oznakaNaplatnogUredaja) = explode ("/", $results[0]->invoice_number);
        $min = $rbr;
        $max = $rbr;
        $storno_racuna = 0; 
        $ukupni_broj_racuna =0;
        $lista_placanja = array("0" => array("nacin_placanja" => 0,"iznos" => 0));    
        $porezna_osnovica = 0;
        $saldo = 0;
        $ukupno_poreza =0;
        $lista_racuna = array();
        //PETLJA ZA POKUPIT POTREBNE VARIJABLE
        foreach ($results as $result) {
            $ukupni_broj_racuna = $ukupni_broj_racuna +1;

            $saldo = $saldo + $result->invoice_total;
            $porezna_osnovica = $porezna_osnovica + $result->invoice_item_subtotal;
            $ukupno_poreza = $ukupno_poreza + $result->invoice_item_tax_total;
            list($rbr1,$oznakaPoslovnogProstora, $oznakaNaplatnogUredaja) = explode ("/", $result->invoice_number);
             array_push($lista_racuna,array('rbr'=>$rbr1,'datum'=>date_create($result->invoice_date_created),'terms'=>$result->client_name,'subtotal'=>$result->invoice_item_subtotal,'np'=>$result->payment_method,'total'=>$result->invoice_total, 'tax'=>$result->invoice_item_tax_total));  


            if ($rbr1 < $min)
            {
                $min = $rbr1;
            }
            if ($rbr1 > $max)
            {
                $max = $rbr1;
            }

            if ($result->creditinvoice_parent_id != null)
            {
            $storno_racuna = $storno_racuna +1;
            }
            if (array_search($result->payment_method,array_column($lista_placanja,"nacin_placanja")) == false )
            {
                 array_push($lista_placanja,array("nacin_placanja"=>$result->payment_method,"iznos"=>$result->invoice_total)); 

            }
            else{
                 foreach ($lista_placanja as &$placanje)
                         { 
                          if ($placanje["nacin_placanja"] == $result->payment_method)
                         {
                         $placanje["iznos"] = $placanje["iznos"]+ $result->invoice_total;
                   }
 
                 }
                 unset($placanje);

            }
                   } ?>
<main>
<h1 class="invoice-title" align="center">Lista izdatih računa</h1>


        <table id="main" class="item-table">
        <tbody>
            <tr>

                <td><?php echo 'Period od: '; ?>
                <?php if($od_datuma) { 
                 echo $od_datuma;
                }
                else {
                    echo date("d-m-Y");
                } ?>    
                <?php echo  'do: '; ?>
                 <?php if($do_datuma) { 
                 echo $do_datuma;
                }
                else {
                    echo date("d-m-Y");
                } ?>    
                </td> 
            </tr>
                   <tr>

                <td><?php echo 'Račun od: '; ?>
                 
                <?php echo $min; ?>
                
             
                <?php echo  'do: '; ?>
            <?php  echo $max;?>    
   </td>
            </tr>
                    <tr>

                <td><?php echo 'Ukupno računa: '; ?>
                 
                <?php echo $ukupni_broj_racuna; ?>
        </td>        
            </tr>

                  <tr>

                <td ><?php echo 'Storno računa: '; ?>
                 
                <?php echo $storno_racuna; ?>
             </td>   
            </tr>
    <tbody class="invoice-sums">
<tr>
                <td class="text-right"><?php echo 'Porezna osnovica: '; ?>
               
                <?php echo format_currency($porezna_osnovica); ?>
            </td>
            </tr> 
<tr> <td class="text-right">
             <?php echo 'Ukupno poreza:  '; ?>
                 
                <?php echo format_currency($ukupno_poreza); ?>
                
                </td>           
   
            </tr>
                    <tr>

                <td class="text-right"><?php echo '<b>Saldo: </b>'; ?>
                 
              <b>  <?php echo format_currency($saldo); ?></b>
                </td>
            </tr>

</tbody>
        </table>
<br/>

<table id="main" class="item-table" style="width:50%;">
        <thead>
    <tr>
        <th class="item-description"> <?php echo 'Vrste plaćanja' ?> </th>
        <th class="item-amount"> <?php echo 'Iznos'; ?> </th>
    </tr>
</thead>
<tbody>
    <?php

                    foreach ($lista_placanja as $payment) {?>
        <?php if ($payment['nacin_placanja'] != 0) { ?>
        <tr>
        <td> <?php 
            switch ($payment['nacin_placanja']){
        case "1":                
            echo 'Novčanice';
            break;
          case "2":                
              echo 'Obustava na plaću';
              break;
          case "3":                
              echo 'Kreditne kartice';
              break;
          case "4":                
              echo 'Transakcijski račun';
              break;
          case "5":                
              echo 'Ostalo';
              break;
          case "6":                
              echo 'Ček';
              break;

            }
?>

 </td>
        <td> <?php echo format_currency($payment['iznos']); ?></td>
        </tr>
        <?php } ?>
        <?php  }

        ?>
</tbody>

</table>



<br />


<table id="main" class="item-table">
        <thead>
    <tr>
        <th > <?php echo 'Rbr' ?> </th>
        <th > <?php echo 'Datum'; ?> </th>
        <th> <?php echo 'Ime'; ?> </th>  
        <th> <?php echo 'Način plać.'; ?> </th>
         <th> <?php echo 'Osnovica'; ?> </th> 
        <th> <?php echo 'Porez'; ?> </th> 
         <th> <?php echo 'Ukupno'; ?> </th>    
    </tr>
</thead>
<tbody>
    <?php
         asort($lista_racuna);
                    foreach ($lista_racuna as $red) {?>
       <tr>
            <td  <?php if($red['total'] <0) { echo 'class="text-red"'; }?>  >
        <?php 
        echo $red['rbr'];
        ?>
           
    </td>
            <td  <?php if($red['total'] <0) { echo 'class="text-red"'; }?>  >
        <?php echo date_format($red['datum'],'d-m-Y'); ?>
        </td>
    <td  <?php if($red['total'] <0) { echo 'class="text-red"'; }?>  >
        <?php echo $red['terms']; ?>
        </td>

        <td  <?php if($red['total'] <0) { echo 'class="text-red"'; }?>  >
        <?php if ($red['np'] != 0) { ?>
        
         <?php 
            switch ($red['np']){
        case "1":                
            echo 'Novčanice';
            break;
          case "2":                
              echo 'Ob. na pla.';
              break;
          case "3":                
              echo 'Kreditne kartice';
              break;
          case "4":                
              echo 'Transakcijski račun';
              break;
          case "5":                
              echo 'Ostalo';
              break;
          case "6":                
              echo 'Ček';
              break;

            }
?>

 </td>       <td <?php if($red['total'] <0) { echo 'class="text-red"'; }?>  > <?php echo format_currency($red['subtotal']); ?></td>
         <td <?php if($red['total'] <0) { echo 'class="text-red"'; }?>  > <?php echo format_currency($red['tax']); ?></td>
    
        <td <?php if($red['total'] <0) { echo 'class="text-red"'; }?>  > <?php echo format_currency($red['total']); ?></td>
               
     </tr>
        <?php } ?>
        <?php  }

        ?>
</tbody>

</table>


<?php } 
else { ?>
            <p> Nema izdatih računa u periodu od: </p>
            <?php echo $od_datuma .' do: '.$do_datuma; ?>
    <?php   } ?>
</main>
       
</body>
</html>
