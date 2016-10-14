<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

class Simtech_Searchanise_Block_Jsinit extends Mage_Core_Block_Text
{
    protected function _toHtml()
    {
        $html = '';

        $searchInputSelector = Mage::helper('searchanise/ApiSe')->getSearchInputSelector();
        if (empty($searchInputSelector)) {
            $searchInputSelector = '#search';
        }

        //
        // Disable standart autocomplete
        //
        $html .=
        "        <script type=\"text/javascript\">
        //<![CDATA[
            try {
                Prototype && Prototype.Version && Event && Event.observe && Event.observe(window, 'load', function()
                {
                    if ($$('{$searchInputSelector}').length) {
                        $$('{$searchInputSelector}')[0].stopObserving('keydown');
                    }
                });
            } catch (e) {}
        //]]>
        </script>
        ";

        $store = Mage::app()->getStore();

        if (!Mage::helper('searchanise/ApiSe')->checkSearchaniseResult(true, $store)) {
            return $html;
        }

        $apiKey = Mage::helper('searchanise/ApiSe')->getApiKey();
        
        if (empty($apiKey)) {
            return $html;
        }

        $seServiceUrl = Mage::helper('searchanise/ApiSe')->getServiceUrl();
        $searchWidgetsLink = Mage::helper('searchanise/ApiSe')->getSearchWidgetsLink(false);

        $restrictBy = '';
        $showOutOfStock = Mage::getStoreConfigFlag(Mage_CatalogInventory_Helper_Data::XML_PATH_SHOW_OUT_OF_STOCK);
        if ($showOutOfStock) {
            // nothing
        } else {
            $restrictBy .= "Searchanise.AutoCmpParams.restrictBy.is_in_stock = '1';";
        }

        $priceFormat = Mage::helper('searchanise/ApiSe')->getPriceFormat($store);
        $priceFormat['after'] = $priceFormat['after'] ? 'true' : 'false';
        
        $html .= 
            "<script type=\"text/javascript\">
            //<![CDATA[
                Searchanise = {};
                Searchanise.host        = '{$seServiceUrl}';
                Searchanise.api_key     = '{$apiKey}';
                Searchanise.SearchInput = '{$searchInputSelector}';
                
                Searchanise.AutoCmpParams = {};
                Searchanise.AutoCmpParams.union = {};
                Searchanise.AutoCmpParams.union.price = {};
                Searchanise.AutoCmpParams.union.price.min = '" . Mage::helper('searchanise/ApiSe')->getCurLabelForPricesUsergroup() . "';

                Searchanise.AutoCmpParams.restrictBy = {};
                Searchanise.AutoCmpParams.restrictBy.status = '1';
                Searchanise.AutoCmpParams.restrictBy.visibility = '3|4';
                {$restrictBy}
                
                Searchanise.options = {};
                Searchanise.AdditionalSearchInputs = '#name,#description,#sku';

                Searchanise.options.ResultsDiv = '#snize_results';
                Searchanise.options.ResultsFormPath = '" . Mage::helper('searchanise')->getResultsFormPath() . "';
                Searchanise.options.ResultsFallbackUrl = '" . $this->getUrl('catalogsearch/result') . "?q=';
                Searchanise.ResultsParams = {};
                Searchanise.ResultsParams.facetBy = {};
                Searchanise.ResultsParams.facetBy.price = {};
                Searchanise.ResultsParams.facetBy.price.type = 'slider';

                Searchanise.ResultsParams.union = {};
                Searchanise.ResultsParams.union.price = {};
                Searchanise.ResultsParams.union.price.min = '" . Mage::helper('searchanise/ApiSe')->getCurLabelForPricesUsergroup() . "';

                Searchanise.ResultsParams.restrictBy = {};
                Searchanise.ResultsParams.restrictBy.visibility = '3|4';

                Searchanise.options.PriceFormat = {
                    decimals_separator:  '" . addslashes($priceFormat['decimals_separator']) . "',
                    thousands_separator: '" . addslashes($priceFormat['thousands_separator']) . "',
                    symbol:              '" . addslashes($priceFormat['symbol']) . "',

                    decimals: '{$priceFormat['decimals']}',
                    rate:     '{$priceFormat['rate']}',
                    after:     {$priceFormat['after']}
                };
                
                (function() {
                    var __se = document.createElement('script');
                    __se.src = '{$searchWidgetsLink}';
                    __se.setAttribute('async', 'true');
                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(__se, s);
                })();
            //]]>
        </script>
        ";
        
        // Uncomment the lines below if it is necessary to hide price in search widget
        // $html .= '
        //     <style type="text/css">
        //         .snize-price {
        //             display: none !important;
        //         }
        //     </style>';

        // Uncomment the lines below if it is necessary to fix size images in widget
        // $html .= '
        //     <style type="text/css">
        //         .snize-item-image {
        //             max-width: 70px !important;
        //         }
        //     </style>';

        return $html;
    }
}
