<div class="table-responsive">
    <table class="table table-striped">

        <thead>
        <tr>
            <th><?php echo trans('status'); ?></th>
<<<<<<< HEAD
            <th><?php echo "Fiskalizacija"; ?></th>
=======
>>>>>>> 84cdfbf65ebf5f859ebe7dfb2bbd6f4bcefda139
            <th><?php echo trans('invoice'); ?></th>
            <th><?php echo trans('created'); ?></th>
            <th><?php echo trans('due_date'); ?></th>
            <th><?php echo trans('client_name'); ?></th>
            <th style="text-align: right;"><?php echo trans('amount'); ?></th>
<<<<<<< HEAD
=======
            <th style="text-align: right;"><?php echo trans('balance'); ?></th>
>>>>>>> 84cdfbf65ebf5f859ebe7dfb2bbd6f4bcefda139
            <th><?php echo trans('options'); ?></th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($invoices as $invoice) {
            if ($this->config->item('disable_read_only') == true) {
                $invoice->is_read_only = 0;
            }
            ?>
            <tr>
                <td>
                    <span class="label <?php echo $invoice_statuses[$invoice->invoice_status_id]['class']; ?>">
                        <?php echo $invoice_statuses[$invoice->invoice_status_id]['label'];
<<<<<<< HEAD
            

=======
>>>>>>> 84cdfbf65ebf5f859ebe7dfb2bbd6f4bcefda139
                        if ($invoice->invoice_sign == '-1') { ?>
                            &nbsp;<i class="fa fa-credit-invoice"
                                     title="<?php echo trans('credit_invoice') ?>"></i>
                        <?php }
                        if ($invoice->is_read_only == 1) { ?>
                            &nbsp;<i class="fa fa-read-only"
                                     title="<?php echo trans('read_only') ?>"></i>
                        <?php }; ?>
<<<<<<< HEAD
                       
                    </span>
                </td>
        
                 <td>
                 <span class="label  <?php if ($invoice->invoice_fiskalizirano == 1) { ?> <?php echo "paid"; ?> <?php } else { ?> <?php echo "viewed"; ?>       <?php }; ?>}">
                        <?php
                        if ($invoice->invoice_fiskalizirano == 1) { ?>
                        <?php echo "Fiskaliziran"; ?>
            
                        <?php }
                        else { ?>
                        <?php echo "Nije fiskaliziran"; ?>
                        <?php }; ?>
                       
                    </span>
                </td>
        
           
               
                
=======
                    </span>
                </td>
>>>>>>> 84cdfbf65ebf5f859ebe7dfb2bbd6f4bcefda139

                <td>
                    <a href="<?php echo site_url('invoices/view/' . $invoice->invoice_id); ?>"
                       title="<?php echo trans('edit'); ?>">
                        <?php echo($invoice->invoice_number ? $invoice->invoice_number : $invoice->invoice_id); ?>
                    </a>
                </td>

                <td>
                    <?php echo date_from_mysql($invoice->invoice_date_created); ?>
                </td>

                <td>
                    <span class="<?php if ($invoice->is_overdue) { ?>font-overdue<?php } ?>">
                        <?php echo date_from_mysql($invoice->invoice_date_due); ?>
                    </span>
                </td>

                <td>
                    <a href="<?php echo site_url('clients/view/' . $invoice->client_id); ?>"
                       title="<?php echo trans('view_client'); ?>">
                        <?php echo $invoice->client_name; ?>
                    </a>
                </td>

                <td class="amount <?php if ($invoice->invoice_sign == '-1') {
                    echo 'text-danger';
                }; ?>">
                    <?php echo format_currency($invoice->invoice_total); ?>
                </td>

<<<<<<< HEAD
=======
                <td class="amount">
                    <?php echo format_currency($invoice->invoice_balance); ?>
                </td>
>>>>>>> 84cdfbf65ebf5f859ebe7dfb2bbd6f4bcefda139

                <td>
                    <div class="options btn-group">
                        <a class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" href="#">
                            <i class="fa fa-cog"></i> <?php echo trans('options'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if ($invoice->is_read_only != 1) { ?>
                                <li>
                                    <a href="<?php echo site_url('invoices/view/' . $invoice->invoice_id); ?>">
                                        <i class="fa fa-edit fa-margin"></i> <?php echo trans('edit'); ?>
                                    </a>
                                </li>
                            <?php } ?>
                            <li>
                                <a href="<?php echo site_url('invoices/generate_pdf/' . $invoice->invoice_id); ?>"
                                   target="_blank">
                                    <i class="fa fa-print fa-margin"></i> <?php echo trans('download_pdf'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo site_url('mailer/invoice/' . $invoice->invoice_id); ?>">
                                    <i class="fa fa-send fa-margin"></i> <?php echo trans('send_email'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="invoice-add-payment"
                                   data-invoice-id="<?php echo $invoice->invoice_id; ?>"
                                   data-invoice-balance="<?php echo $invoice->invoice_balance; ?>"
                                   data-invoice-payment-method="<?php echo $invoice->payment_method; ?>">
                                    <i class="fa fa-money fa-margin"></i>
                                    <?php echo trans('enter_payment'); ?>
                                </a>
                            </li>
                            <?php if ($invoice->invoice_status_id == 1 || ($this->config->item('enable_invoice_deletion') === true && $invoice->is_read_only != 1)) { ?>
                                <li>
                                    <a href="<?php echo site_url('invoices/delete/' . $invoice->invoice_id); ?>"
                                       onclick="return confirm('<?php echo trans('delete_invoice_warning'); ?>');">
                                        <i class="fa fa-trash-o fa-margin"></i> <?php echo trans('delete'); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>

    </table>
<<<<<<< HEAD
</div>
=======
</div>
>>>>>>> 84cdfbf65ebf5f859ebe7dfb2bbd6f4bcefda139
