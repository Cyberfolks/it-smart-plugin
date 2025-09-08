jQuery(document).ready(function($){
    // $(document).on('click', '.wc-generate-doc', function(e){
    //     e.preventDefault();

    //     var orderId = $(this).data('order');
    //     var docType = $(this).data('doc');

    //     $.post(wc_invoice_packing.ajax_url, {
    //         action: 'wc_generate_document',
    //         nonce: wc_invoice_packing.nonce,
    //         order_id: orderId,
    //         doc_type: docType
    //     }, function(response){
    //         if(response.success){
    //             var win = window.open("", "PrintWindow");
    //             win.document.write(response.data.html);
    //             win.document.close();
    //             win.focus();
    //             win.print();
    //         } else {
    //             alert('Error generating document');
    //         }
    //     });
    // });
});
