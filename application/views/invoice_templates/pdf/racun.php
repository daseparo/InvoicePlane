<html lang="<?php echo trans('cldr'); ?>">
<head>
    <meta charset="utf-8">
    <title><?php echo trans('invoice'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/default/css/templates.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/default/css/custom-pdf.css">
</head>
<body>
<header class="clearfix">

    <div id="logo">
        <?php echo invoice_logo_pdf(); ?>
    </div>

    <div id="client">
       <?php if ($invoice->client_name != "0_PRAZNO") { ?>
        <div>
           <?php echo lang('bill_to'); ?>:<br /> 
            <b><?php echo $invoice->client_name; ?></b>
        </div>
        <?php if ($invoice->client_vat_id) {
            echo '<div>' . trans('vat_id_short') . ': ' . $invoice->client_vat_id . '</div>';
        }
        if ($invoice->client_tax_code) {
            echo '<div>' . trans('tax_code_short') . ': ' . $invoice->client_tax_code . '</div>';
        }
        if ($invoice->client_address_1) {
            echo '<div>' . $invoice->client_address_1 . '</div>';
        }
        if ($invoice->client_address_2) {
            echo '<div>' . $invoice->client_address_2 . '</div>';
        }
        if ($invoice->client_city && $invoice->client_zip) {
            echo '<div>' . $invoice->client_city . ' ' . $invoice->client_zip . '</div>';
        } else {
            if ($invoice->client_city) {
                echo '<div>' . $invoice->client_city . '</div>';
            }
            if ($invoice->client_zip) {
                echo '<div>' . $invoice->client_zip . '</div>';
            }
        }
        if ($invoice->client_state) {
            echo '<div>' . $invoice->client_state . '</div>';
        }
        if ($invoice->client_country) {
            echo '<div>Hrvatska</div>';
        }

        echo '<br/>';

        if ($invoice->client_phone) {
            echo '<div>' . trans('phone_abbr') . ': ' . $invoice->client_phone . '</div>';
        } ?>
        <?php } ?>
    </div>
    <div id="company">
        <div><b><?php echo $invoice->user_company; ?></b></div>
        <?php if ($invoice->user_vat_id) {
            echo '<div>' . trans('vat_id_short') . ': ' . $invoice->user_vat_id . '</div>';
        }
        if ($invoice->user_tax_code) {
            echo '<div>' . trans('tax_code_short') . ': ' . $invoice->user_tax_code . '</div>';
        }
         if ($invoice->user_custom_iban) {
            echo '<div>IBAN: ' . $invoice->user_custom_iban .'</div>';
        }
 
        if ($invoice->user_address_1) {
            echo '<div>' . $invoice->user_address_1 .'</div>';
        }
        if ($invoice->user_address_2) {
            echo '<div>' . $invoice->user_address_2 . '</div>';
        }
        if ($invoice->user_city && $invoice->user_zip) {
            echo '<div>' . $invoice->user_city . ' ' . $invoice->user_zip . '</div>';
        } else {
            if ($invoice->user_city) {
                echo '<div>' . $invoice->user_city . '</div>';
            }
            if ($invoice->user_zip) {
                echo '<div>' . $invoice->user_zip . '</div>';
            }
        }
        if ($invoice->user_state) {
            echo '<div>' . $invoice->user_state . '</div>';
        }
        if ($invoice->user_country) {
            echo '<div> Hrvatska</div>';
        }

        if ($invoice->user_phone) {
            echo '<div>' . trans('phone_abbr') . ': ' . $invoice->user_phone . '</div>';
        }
        if ($invoice->user_fax) {
            echo '<div>' . trans('fax_abbr') . ': ' . $invoice->user_fax . '</div>';
        }
        ?>
    </div>

</header>

<main>

       <h1 class="invoice-title" align="center"><?php echo trans('invoice') . ' ' . $invoice->invoice_number; ?></h1>

    <table id="main" class="item-table">
        <thead>
        <tr>
            <th class="item-desc"><?php echo trans('description'); ?></th>
            <th class="item-name"><?php echo trans('item'); ?></th>
            
            <th class="item-amount text-right"><?php echo trans('qty'); ?></th>
            <th class="item-price text-right"><?php echo trans('price'); ?></th>
            <?php if ($show_discounts) : ?>
                <th class="item-discount text-right"><?php echo trans('discount'); ?></th>
            <?php endif; ?>
             <th class="item-total text-right"><?php echo trans('subtotal'); ?></th>

            <th class="item-total text-right"><?php echo trans('total_vat'); ?></th>
        </tr>
        </thead>
        <tbody>

        <?php
        foreach ($items as $item) { ?>
            <tr>
                  <td><?php echo nl2br($item->item_description); ?></td>
                <td><?php echo $item->item_name; ?></td>
              
                <td class="text-right">
                    <?php echo format_amount($item->item_quantity); ?>
                </td>
                <td class="text-right">
                    <?php echo format_currency($item->item_price); ?>
                </td>
                <?php if ($show_discounts) : ?>
                    <td class="text-right">
                        <?php echo format_currency($item->item_discount); ?>
                    </td>
                <?php endif; ?>
                <td class="text-right">
                    <?php echo format_currency($item->item_subtotal-$item->item_discount); ?>
                </td>

                <td class="text-right">
                    <?php echo format_currency($item->item_total); ?>
                </td>
            </tr>
        <?php } ?>

        </tbody>
        <tbody class="invoice-sums">

        
    <?php 
    $lista_poreza = array ("0"=>array("stopa" => 0,"osnovica"=>0,"iznos_poreza"=> 0));
    $imapdv=false;
    foreach ($items as $stavka)
    {
        if (strpos($stavka->item_tax_rate_name, 'PDV') !== false)
        {      
           
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

             }
            unset($porez);
        $imapdv=true; 
         }
 
     }
     ?>
     <?php
     if ($imapdv)
     {

     foreach ($lista_poreza as $porez1)
     { 
 
         if ($porez1["stopa"] != 0)
         {?> 
            <tr>
                <td <?php echo($show_discounts ? 'colspan="6"' : 'colspan="5"'); ?> class="text-right">
                  <b>  <?php echo trans('osnovica').' '.$porez1["stopa"]."%"; ?></b>
                </td>
                <td class="text-right">
               <b>     <?php echo format_currency($porez1["osnovica"]); ?></b>
                </td>
            </tr>
             <tr>
                <td <?php echo($show_discounts ? 'colspan="6"' : 'colspan="5"'); ?> class="text-right">
               <b>     <?php echo trans('vat').' '.$porez1["stopa"],"%"; ?></b>
                </td>
                <td class="text-right">
                <b>    <?php echo format_currency($porez1["iznos_poreza"]); ?></b>
                </td>
            </tr>
  

        <?php
          } 
      }
      }
        else {?>
                           <tr>
            <td <?php echo($show_discounts ? 'colspan="6"' : 'colspan="5"'); ?> class="text-right">
                <b><?php echo trans('osnovica'); ?></b>
            </td>
            <td class="text-right">
                <b><?php echo format_currency($invoice->invoice_item_subtotal); ?></b>
            </td>
        </tr>
            <tr>
            <td <?php echo($show_discounts ? 'colspan="6"' : 'colspan="5"'); ?> class="text-right">
                <b><?php echo trans('vat'); ?></b>
            </td>
            <td class="text-right">
                <b><?php echo format_currency($invoice->invoice_item_tax_total); ?></b>
            </td>
        </tr>

                                            
                             
               <?php } 
            ?>


        <tr>
            <td <?php echo($show_discounts ? 'colspan="6"' : 'colspan="5"'); ?> class="text-right">
                <b><?php echo trans('total'); ?></b>
            </td>
            <td class="text-right">
                <b><?php echo format_currency($invoice->invoice_total); ?></b>
            </td>
        </tr>
        </tbody>
    </table>
 <div class="invoice-details clearfix">
        <table>
            <tr>
                 <td class="texleft color-n">
                                        <?php echo 'Datum i vrijeme računa'; ?>: &nbsp;
                                        <?php echo date_from_mysql($invoice->invoice_date_created, TRUE); ?>
                     <?php echo $invoice->invoice_time_created; ?>
                        |
                                        <?php echo lang('due_date'); ?>: &nbsp;
                                        <?php echo date_from_mysql($invoice->invoice_date_due, TRUE); ?>
                                    </td>
            </tr>
        <tr>
        <td class="texleft color-n">
                                    <?php echo 'Mjesto i datum isporuke: Blato,'; ?>&nbsp;
                                    <?php echo date_from_mysql($invoice->invoice_date_created, TRUE); ?>
               |
                                    <?php echo 'Blagajnik:' ?>&nbsp;
                                    <?php echo $invoice->user_name; ?> 
            </td>
            </tr>
            <?php if($invoice->invoice_zki) { ?>
            <tr>
           <td class="texleft color-n">
                                    <?php echo 'ZKI:'; ?>&nbsp;
                                    <?php echo $invoice->invoice_zki; ?>
        <?php if($invoice->invoice_jir) { ?>   |
                                    <?php echo 'JIR:' ?>&nbsp;
                                    <?php echo $invoice->invoice_jir; ?> <?php } ?>
            </td>
            </tr>
              <?php   } ?>

         <tr>
                  <td class="texleft color-n">
                                        <?php echo 'Način plaćanja'; ?>&nbsp;
                                        <?php echo $payment_method->payment_method_name; ?>
                </td>
                </tr>
       
 
        </table>
    </div>


</main>

<footer>
    <?php if ($invoice->invoice_terms) : ?>
        <div class="textleft color-n">
            <b><?php echo trans('uvjeti'); ?></b><br/>
            <?php echo nl2br($invoice->invoice_terms); ?>
        </div>
    <?php endif; ?>
</footer>
</body>
</html>
