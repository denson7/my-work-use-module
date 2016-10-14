

function showPointDetailsViewPage(){
    $$('.catalog-points-details').invoke('show');
    $$('.show-details-points-url').invoke('hide');
    $$('.hide-details-points-url').invoke('show');
}

function hidePointDetailsViewPage(){
    $$('.catalog-points-details').invoke('hide');
    $$('.show-details-points-url').invoke('show');
    $$('.hide-details-points-url').invoke('hide');
}

function getTierPriceCorrection(qty, default_points){
    //j2t_math_points
    qty = (qty) ? qty : 1;
    var return_value = default_points;
    if(json_tier_prices.length > 0){
        for (var k=0; k < json_tier_prices.length; k++) {
            if (qty >= json_tier_prices[k]['price_qty']){
                return_value = json_tier_prices[k]['productTierPoints'];
            }
        }
    }
    return return_value;
}

function checkJ2tPoints(){
    var points = $('j2t-pts').innerHTML;
    if (points > 0){
        $$('.j2t-loyalty-points').invoke('show');
    } else {
        $$('.j2t-loyalty-points').invoke('hide');
    }
    modifyJ2tEquivalence($('j2t-pts').innerHTML);
    checkJ2tCloneText();
}

function checkJ2tCloneText(){
    if (typeof is_loaded_information !== 'undefined' && $('j2t-points-clone') && $$(".j2t-loyalty-points").length > 0){
        $('j2t-points-clone').style.display = $$(".j2t-loyalty-points")[0].style.display;
        var text_clone = $$(".j2t-loyalty-points")[0].innerHTML;
        text_clone = text_clone.replace("j2t-pts", "j2t-pts-clone");
        text_clone = text_clone.replace("j2t-point-equivalence", "j2t-point-equivalence-clone");
        $('j2t-points-clone').innerHTML = text_clone;
    }
}

Number.prototype.j2tFormatMoney = function(c, d, t){
    var n = this, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

function modifyJ2tEquivalence(current_points) {
    if ($$(".j2t-point-equivalence").length > 0){
        var money_equivalence = current_points * j2t_point_default_point_unit * j2t_point_currency_base;
        money_equivalence = Math.round(money_equivalence * 100)/100;
        money_equivalence = j2t_point_currency.replace("__MONEY__", (money_equivalence).j2tFormatMoney(2, j2t_dec_sep, j2t_mil_sep));
        var return_value = j2t_point_equivalence_txt.replace("1111", current_points);
        return_value = return_value.replace("2222", money_equivalence);
        $$(".j2t-point-equivalence")[0].innerHTML = return_value;
    }
}

document.observe("dom:loaded", function() { 
    if (typeof is_loaded_information !== 'undefined' && $('j2t-pts')) {
        var product_downloadable   = $$('.product-downloadable-link');
        if (product_downloadable.length > 0){
            product_downloadable.each(function(element){
                Event.observe(element, 'change', function() {
                    if (this.checked){
                        $('j2t-pts').innerHTML = Math.ceil(j2t_points + parseFloat(json_credit_downloadable[this.value]) + j2t_options);
                    } else {
                        $('j2t-pts').innerHTML = Math.ceil(j2t_points + j2t_options);
                    }
                    checkJ2tPoints();
                });
            });
        }
    }
});

document.observe("dom:loaded", function() {
    if (typeof is_loaded_information !== 'undefined' && typeof is_bundle !== 'undefined' && is_bundle && $('j2t-pts')){
        $$('#super-product-table .qty').each(function(element){
            if (element.nodeName == 'SELECT'){
                Event.observe(element, 'change', function() {
                    process_bundle_credit();
                });
            } else {
                Event.observe(element, 'keyup', function() {
                    process_bundle_credit();
                });
            } 
        });
        process_bundle_credit();
    }
});

function bundle_extra_points(points_passed) {
    var extra = 0;
    if (typeof bundle_rules !== 'undefined' && bundle_rules != null){
        for (var i = 0; i < bundle_rules.length; i++){
            //add or remove points
            if(bundle_rules[i].action_type == 1){
                extra += bundle_rules[i].points;
            }
        }
    }
    return extra;
}

function bundle_muldiv_points(points_passed) {
    if (typeof bundle_rules !== 'undefined' && bundle_rules != null){
        for (var i = 0; i < bundle_rules.length; i++){
            //add or remove points
            if (bundle_rules[i].action_type == -1 /*&& !bundle_fixed_price*/){ //multiply by
                points_passed = points_passed * bundle_rules[i].points;
            } else if (bundle_rules[i].action_type == -2 /*&& !bundle_fixed_price*/){ //divide by
                points_passed = points_passed / bundle_rules[i].points;
            }
        }
    }
    return points_passed;
}

function process_bundle_credit(){
    var points_bundle = 0;
    var qty_bundle = 0;
    $$('#super-product-table .qty').each(function(element){ 
        element.name.sub(/[0-9]+/, function(match){
            optionId = match[0];
        });
        
        if (isNaN(parseFloat(element.value))) {
            qty_bundle = 0;
        } else {
            qty_bundle = parseFloat(element.value);
        }
        if (qty_bundle > 0){
            points_bundle += (qty_bundle * json_credit_grouped[optionId]);
        }
    });
    
    points_bundle += bundle_extra_points();

    $('j2t-pts').innerHTML = Math.ceil(points_bundle + j2t_options);
    checkJ2tPoints();
}

////////////////////////////////////////////////////////////////////////////////////////

document.observe("dom:loaded", function() {
    if (typeof is_loaded_information !== 'undefined' && typeof isCustomPoints !== 'undefined' && isCustomPoints && $('j2t-pts')){
        if ($('qty')){
            Event.observe($('qty'), 'keyup', function(){ processPointsSelects() });
        }
        var product_settings   = $$('.super-attribute-select');
        processPointsSelects = function () {
            if (product_settings.length > 0){
                var concat_val = '';
                j2t_points = 0;
                var dont_process_it = false;
                $$('.super-attribute-select').each(function(el){
                    if(el.value == ''){
                        dont_process_it = true;
                    }
                    if (concat_val != ''){
                        concat_val += '|'+el.value;
                    } else {
                        concat_val = el.value;
                    }
                });
                if (!dont_process_it && concat_val != ''){
                    //load points in ajax according to attributes
                    var used_qty = 0;
                    if ($('qty')){
                        used_qty = $('qty').value;
                    }
                    used_qty = (used_qty <= 0 || used_qty == "" || isNaN(used_qty)) ? 1 : used_qty;
                    if (json_credit[concat_val] != undefined){
                        j2t_points = json_credit[concat_val];
                        if (json_credit[concat_val+'|tierPrice'] != undefined){
                            var tierprices = json_credit[concat_val+'|tierPrice'];
                            if(tierprices.length > 0){
                                for (var k=0; k < tierprices.length; k++) {
                                    if (used_qty >= tierprices[k]['price_qty']){
                                        j2t_points = tierprices[k]['productTierPoints'];
                                    }
                                }
                            }
                        }
                        $('j2t-pts').innerHTML = j2t_math_points(used_qty, j2t_points, false);
                        checkJ2tPoints();
                    }
                } else {
                    //if (!isNaN($('qty').value)) {$('j2t-pts').innerHTML = j2t_math_points($('qty').value, j2t_points, true); } checkJ2tPoints();
                }
            } else {
                var test_qty = 0;
                if ($('qty')){
                    test_qty = $('qty').value;
                }                
                if (!isNaN(test_qty)) {$('j2t-pts').innerHTML = j2t_math_points(test_qty, j2t_points, true); } checkJ2tPoints();
            }
        }
        
        //Event.observe(window, 'load', function() {
            if(typeof spConfig != 'undefined'){
                var oldSpConfigure = spConfig.configureElement;
                /*spConfig.configureElement = extendedElementConfig;
                function extendedElementConfig(el) {
                    oldSpConfigure.apply(spConfig, [el]);
                    processPointsSelects();
                }*/
                spConfig.configureElement = function (el){
                    oldSpConfigure.apply(spConfig, [el]);
                    processPointsSelects();
                };
            }
        //});
    } else if (typeof is_loaded_information !== 'undefined' && typeof j2t_points !== 'undefined' && $('j2t-pts')) {
        if ($('qty')){
            Event.observe($('qty'), 'keyup', function(){ if (!isNaN($('qty').value)) {$('j2t-pts').innerHTML = j2t_math_points($('qty').value, j2t_points, true); } checkJ2tPoints();});
        }
    }
});

function j2t_math_points(qty, pts_changed, tierprice_verification){
    if (tierprice_verification){
        pts_changed = getTierPriceCorrection(qty, pts_changed);
    }
    var val_return = 0;
    if (isNaN(parseFloat(qty))) {
        qty = 1;
    }
    if(qty > 0){
        val_return = (pts_changed + j2t_options) * qty;
    } else if(pts_changed > 0) {
        val_return = pts_changed + j2t_options;
    }//only options 
    else if(j2t_options > 0) {
        val_return = j2t_options;
    }
    return Math.ceil(val_return);
}



////////////////////////// BUNDLE //////////////////////////////

function j2t_points_bundle(){
    var pts = 0;
    bundle_select.each(function(element){
        var el_val_temp = $F(element.id);

        if (el_val_temp.constructor.toString().indexOf("Array") != -1){
            //multiple
            var el_array = el_val_temp;
            if (el_array.length > 0){
                for (var k=0; k < el_array.length; k++) {
                    var el_val = el_array[k];
                    if (el_val != ''){
                        var qty = 1;
                        var id_qty = 'bundle-option-'+json_credit_bundle[el_val]['optionId']+'-qty-input';
                        if ($(id_qty)){
                            if ($(id_qty).value > 0){
                                qty = $(id_qty).value;
                            }
                        }
                        
                        pts += bundle_muldiv_points(json_credit_bundle[el_val]['points'] * qty);
                    }
                }
            }
        } else {
            //normal
            var el_val = el_val_temp;
            if (el_val != ''){
                var qty = 1;
                var id_qty = 'bundle-option-'+json_credit_bundle[el_val]['optionId']+'-qty-input';
                if ($(id_qty)){
                    if ($(id_qty).value > 0){
                        qty = $(id_qty).value;
                    }
                }
                pts += bundle_muldiv_points(json_credit_bundle[el_val]['points'] * qty);
            }
        }
    });

    bundle_radio.each(function(element){
        if (element.checked && typeof(json_credit_bundle[element.value]) != 'undefined'){
            var qty = 1;

            var id_qty = 'bundle-option-'+json_credit_bundle[element.value]['optionId']+'-qty-input';
            if ($(id_qty)){
                if ($(id_qty).value > 0){
                    qty = $(id_qty).value;
                }
            }
            pts += bundle_muldiv_points(json_credit_bundle[element.value]['points'] * qty);
        }
    });

    bundle_checkbox.each(function(element){
        if (element.checked && typeof(json_credit_bundle[element.value]) != 'undefined'){
            var qty = 1;
            var id_qty = 'bundle-option-'+json_credit_bundle[element.value]['optionId']+'-qty-input';
            if ($(id_qty)){
                if ($(id_qty).value > 0){
                    qty = $(id_qty).value;
                }
            }
            pts += bundle_muldiv_points(json_credit_bundle[element.value]['points'] * qty);
        }
    });

    bundle_hidden.each(function(element){ 
        var qty = 1;
        var id_qty = 'bundle-option-'+json_credit_bundle[element.value]['optionId']+'-qty-input';
        //alert(id_qty);
        if ($(id_qty)){
            if ($(id_qty).value > 0){
                qty = $(id_qty).value;
            }
        }
        pts += bundle_muldiv_points(json_credit_bundle[element.value]['points'] * qty);
    });
    
    pts_changed = pts+j2t_points;
    var test_qty = 0;
    if ($('qty')){
        test_qty = $('qty').value;
    }
    $('j2t-pts').innerHTML = j2t_math_points(test_qty, pts_changed, false);
    checkJ2tPoints();
    return pts;
}

var bundle_select, bundle_radio, bundle_checkbox, bundle_hidden;
document.observe("dom:loaded", function() {
    if (typeof is_loaded_information !== 'undefined' && typeof is_bundle !== 'undefined' && is_bundle && $('j2t-pts')){
        
        if(typeof bundle != 'undefined'){
            var oldBundleChange = bundle.changeSelection;
            /*bundle.changeSelection = extendedBundleSelection;
            function extendedBundleSelection(el) {
                oldBundleChange.apply(bundle, [el]);
                j2t_points_bundle();
            }*/
            bundle.changeSelection = function (el){
                oldBundleChange.apply(bundle, [el]);
                j2t_points_bundle();
            };
            //bundle.changeSelection(this)
            
            var oldBundleChangeQty = bundle.changeOptionQty;
            /*bundle.changeOptionQty = extendedBundleQtyOption;
            function extendedBundleQtyOption(el, event) {
                oldBundleChangeQty.apply(bundle, [el, event]);
                j2t_points_bundle();
            }*/
            bundle.changeOptionQty = function (el, event){
                oldBundleChangeQty.apply(bundle, [el, event]);
                j2t_points_bundle();
            };
            //bundle.changeOptionQty(this, event)
        }
        
        
        bundle_hidden = $$('#product-options-wrapper input[type="hidden"]');	
        bundle_select   = $$('.bundle-option-select');
        bundle_radio   = $$('.product-options .radio');
        bundle_checkbox   = $$('.product-options .checkbox');
        var qties = $$('.qty');
        j2t_points_bundle();
    }
});

////////////////////////// /BUNDLE //////////////////////////////

function calculateOptionsPrice(){
    j2t_options = reloadCreditOption();
    var test_qty = 0;
    if ($('qty')){
        test_qty = $('qty').value;
    }
    if (typeof(json_credit_bundle) != 'undefined'){
        var bundle_pts = j2t_points_bundle();
        $('j2t-pts').innerHTML = j2t_math_points(test_qty, j2t_points + bundle_pts, true);
    } else {
        $('j2t-pts').innerHTML = j2t_math_points(test_qty, j2t_points, true);
    }
    checkJ2tPoints();
}


document.observe("dom:loaded", function() {
    if (typeof is_loaded_information !== 'undefined' && $('j2t-pts')){
        option_select   = $$('.product-custom-option');

        if(typeof opConfig != 'undefined'){
            var oldOpReloadPrice = opConfig.reloadPrice;
            /*function extendedReloadPrice() {
                oldOpReloadPrice.apply(opConfig);
                calculateOptionsPrice();
            }*/
            //opConfig.reloadPrice = extendedReloadPrice;
            opConfig.reloadPrice = function (){
                oldOpReloadPrice.apply(opConfig);
                calculateOptionsPrice();
            };
            //opConfig.reloadPrice()
        }
        calculateOptionsPrice();
    }
});



function reloadCreditOption(){
    var optionPts = 0;
    if (isCustomPoints){
        config = json_option_credit;
        skipIds = [];
        $$('.product-custom-option').each(function(element){
            var optionId = 0;
            element.name.sub(/[0-9]+/, function(match){
                optionId = match[0];
            });
            if (config[optionId]) {
                if (element.type == 'checkbox' || element.type == 'radio') {
                    if (element.checked) {
                        if (config[optionId][element.getValue()]) {
                            optionPts += parseFloat(config[optionId][element.getValue()]);
                        }
                    }
                } else if(element.hasClassName('datetime-picker') && !skipIds.include(optionId)) {
                    dateSelected = true;
                    $$('.product-custom-option[id^="options_' + optionId + '"]').each(function(dt){
                        if (dt.getValue() == '') {
                            dateSelected = false;
                        }
                    });
                    if (dateSelected) {
                        optionPts += parseFloat(config[optionId]);
                        skipIds[optionId] = optionId;
                    }
                } else if(element.type == 'select-one' || element.type == 'select-multiple') {
                    if (element.options) {
                        $A(element.options).each(function(selectOption){
                            if (selectOption.selected) {
                                if (config[optionId][selectOption.value]) {
                                    optionPts += parseFloat(config[optionId][selectOption.value]);
                                }
                            }
                        });
                    }
                } else {
                    if (element.getValue().strip() != '') {
                        optionPts += parseFloat(config[optionId]);
                    }
                }
            }
        });
    }
    return optionPts;
}
checkJ2tCloneText();