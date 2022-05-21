(function ($) {
    $('#shipping_state').on('change', function () {
        var state_text = $(this).find('option:selected').text();
        
        if (state_text != '') {
            $.ajax({
                type: 'POST',
                url: woocommerce_params.ajax_url,
                data: {
                    action: 'popaket_on_state_change',
                    state: state_text,
                },
                success: function (res) {
                    $('#shipping_city_field .woocommerce-input-wrapper').empty().html(`
                        <select name="shipping_city" id="shipping_city" class="state_select" data-placeholder="Pilih Kota">
                            <option value=""></option>
                            ${res.data.map(item => `<option value="${item.city_id}|${item.city_name}">${item.city_name}</option>`).join('')}
                        </select>
                    `);
                    $('#shipping_city').select2();

                    $('#shipping_district_field .woocommerce-input-wrapper').empty().html(`
                        <input class="input-text form-control-lg border-0 bg-secondary bg-opacity-10 fs-6" type="text" name="shipping_district" id="shipping_district" class="district_select" placeholder="Input District">
                    `);
                    $('#shipping_subdistrict_field .woocommerce-input-wrapper').empty().html(`
                        <input class="input-text form-control-lg border-0 bg-secondary bg-opacity-10 fs-6" type="text" name="shipping_subdistrict" id="shipping_subdistrict" class="subdistrict_select" placeholder="Input Sub District">
                    `);
                }
            });
        }
    })

    $('#shipping_city_field').on('change', '#shipping_city', function () {
        var city_id = $(this).val();

        if (city_id != '') {
            $.ajax({
                type: 'POST',
                url: woocommerce_params.ajax_url,
                data: {
                    action: 'popaket_on_city_change',
                    city: city_id,
                },
                success: function (res) {
                    $('#shipping_district_field .woocommerce-input-wrapper').empty().html(`
                        <select name="shipping_district" id="shipping_district" class="district_select" data-placeholder="Pilih Distrik">
                            <option value=""></option>
                            ${res.data.map(item => `<option value="${item.district_id}|${item.district_name}">${item.district_name}</option>`).join('')}
                        </select>
                    `);
                    $('#shipping_district').select2();

                    $('#shipping_subdistrict_field .woocommerce-input-wrapper').empty().html(`
                        <input class="input-text form-control-lg border-0 bg-secondary bg-opacity-10 fs-6" type="text" name="shipping_subdistrict" id="shipping_subdistrict" class="subdistrict_select" placeholder="Input Sub District">
                    `);
                }
            });
        }
    })

    $('#shipping_district_field').on('change', '#shipping_district', function () {
        var district_id = $(this).val();

        if (district_id != '') {
            $.ajax({
                type: 'POST',
                url: woocommerce_params.ajax_url,
                data: {
                    action: 'popaket_on_district_change',
                    district: district_id,
                },
                success: function (res) {
                    $('#shipping_subdistrict_field .woocommerce-input-wrapper').empty().html(`
                        <select name="shipping_subdistrict" id="shipping_subdistrict" class="subdistrict_select" data-placeholder="Pilih Sub Distrik">
                            <option value=""></option>
                            ${res.data.map(item => `<option value="${item.sub_district_id}|${item.sub_district_name}">${item.sub_district_name}</option>`).join('')}
                        </select>
                    `);
                    $('#shipping_subdistrict').select2();
                }
            });
        }
    })

    $('.woocommerce-checkout-review-order').on('change', 'input[name=payment_method]', function () {
        $('body').trigger('update_checkout');
    })

    $('#shipping_insurance').on('click', function() {
        $('body').trigger('update_checkout');
    })
})(jQuery)