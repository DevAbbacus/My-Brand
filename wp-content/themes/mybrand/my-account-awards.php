<?php 
/**
 * template name: Customer Curremt RMS Invoices
 */
//get_header();


    $user_id = get_current_user_id();
    $customer = new WC_Customer( $user_id );

    $customer_billing_email = $customer->billing_email;
    global $wpdb;
    $local_wp_crms_member = $wpdb->get_results("SELECT * FROM wp_crms_member WHERE email='".$customer_billing_email."'");

    $crms_mem_id = $local_wp_crms_member[0]->id;


    $url = "https://api.current-rms.com/api/v1/opportunities?q[member_id_eq]=".$crms_mem_id."&filtermode=live&page=1&per_page=5000";

    
    $ch = curl_init( $url );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, "CURL_HTTP_VERSION_1_1");
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('X-SUBDOMAIN:o3group','X-AUTH-TOKEN:PDW-dNfDiZd5RbLLbHYT','Content-Type:application/json'));
    # Return response instead of printing.
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    # Send request.
    $result = curl_exec($ch);
    curl_close($ch);
    # Print response.
    $result = json_decode($result);


   //echo "<pre>";print_r($result);exit();
       
        $invoices_array = $result->opportunities;
        //echo "<pre>";print_r($invoices_array);exit();


        foreach ($invoices_array as $key => $invoice_array) {


            $participants_array = $invoice_array->participants;

            $list_data = "<ul>";    
            foreach ($participants_array as $key => $array_data) {

                $list_data .= "<li>".$array_data->member_name."</li>";
                
            }
            $list_data .= "</ul>";
                //echo "<pre>";print_r($list_data);exit();

               
            $dt = new DateTime($invoice_array->updated_at);
            $date = $dt->format('Y-m-d');
            
            $url = site_url();
            $button = $url."/my-account/my-custom-endpoint/".$invoice_array->id."/?lang=en";

            $totoal_invoice_array[] = array($invoice_array->id,$list_data,$invoice_array->subject,$date, $invoice_array->charge_excluding_tax_total,"<button id='show-table' class='show_table btn btn-secondary'><a href='$button'>View Products</a></button>" );
        }
        $json_encode_req = json_encode($totoal_invoice_array);

        


?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
<table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Invoice ID</th>
                <th>Name of the Participant</th>           
                <th>Invoice Subject</th>
                <th>Invoice Date</th>
                <th>Invoice Charge Total</th>                
                <th>See products</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <th>Invoice ID</th>
                <th>Name of the Participant</th>           
                <th>Invoice Subject</th>
                <th>Invoice Date</th>
                <th>Invoice Charge Total</th>                
                <th>See products</th>
            </tr>
        </tfoot>
    </table>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script> 
        var dataSet = <?php echo $json_encode_req; ?>;
        jQuery(document).ready(function() {
            jQuery('#example').DataTable( {
                data: dataSet,
                columns: [
                    { title: "Invoice Id" },
                    { title: "Name of the Participant" },
                    { title: "Invoice Subject" },
                    { title: "Invoice Date" },
                    { title: "Invoice Charge Total" },                    
                    { title: "See products" }                   
                ]
            } );
        } );
    </script>


<?php 
//get_footer();
?>