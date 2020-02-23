var noticeTimer,cart_notice;

cart_notice = document.querySelector('#woo_ajax_notice');
cart_notice.classList.add(popup_position, popup_style); // Add classes from the plugin settings

document.querySelector('.single_add_to_cart_button').addEventListener('click', function(e){ 

    btn_clicked = this;
    parent_form = btn_clicked.form;

    // Check if the product is not a GROUPED product
    if ( !parent_form.classList.contains('grouped_form') ) {
        
        e.preventDefault(); 

        var btn_clicked,product_data,parent_form,quantity_selected,product_quantity,temp_var_product_id,product_id,variation_id;

        // IF Simple Product
        temp_product_id = this.value;
        // IF Variable Product
        temp_var_product_id = parent_form.querySelector('input[name="product_id"]');
        product_id = (temp_var_product_id) ? temp_var_product_id.value : temp_product_id;
        // Get the selected/entered quantity - default to 1
        quantity_selected = parent_form.querySelector('input[name="quantity"]').value
        product_quantity = (quantity_selected) ? quantity_selected : 1;
        // Get the variation id if product is a variable product - default to 0
        variable_product = parent_form.querySelector('input[name="variation_id"]');
        variation_id = (variable_product) ? variable_product.value : 0;

        product_data = 'action=codebyksalting_woo_simple_ajax&product_id='+product_id+'&quantity='+product_quantity+'&variation_id='+variation_id;
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', wc_add_to_cart_params.ajax_url);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.onload = function() {
            if (xhr.status === 200) {
                cart_notice.classList.toggle('show');
                // Set the timer according to the plugin settings
                noticeTimer = setTimeout(
                    function(){
                        cart_notice.classList.toggle('show');
                    },
                    popup_duration
                );
            }
        };
        xhr.onerror = function() {
            console.log('An error occured and the request could not be completed.');
        };
        xhr.send(encodeURI(product_data));

    }

}); // document.querySelector('.single_add_to_cart_button')

// Clear the timer and hide the notice
document.querySelector('.btn-continue').addEventListener('click', function(e){
    e.preventDefault();
    clearTimeout(noticeTimer);
    cart_notice.classList.toggle('show');
}); // document.querySelector('.btn-continue')
