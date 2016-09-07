<?php

class Magestore_Shopbybrand_Block_Adminhtml_Brand_Edit_Tab_Report_Graph extends Mage_Adminhtml_Block_Dashboard_Graph {

    protected $_rate_chart_money;
    protected $_google_chart_params = array(
        'cht' => 'lc',
        'chf' => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
        'chm' => 'B,f4d4b2,0,0,0',
        'chco' => 'db4814',
    );
    protected $_width = '750';
    protected $_height = '300';

    public function __construct() {
        $data = $this->getDataLifetime();
        $this->_rate_chart_money = round($data['totals_money'] * 2 / $data['totals_qty']);
        Mage::register('rate_chart_money', $this->_rate_chart_money);
        $currency = Mage::app()->getLocale()->currency(Mage::app()->getStore()->
                                getBaseCurrencyCode())->getSymbol();
        $this->_google_chart_params = array(
            'cht' => 'lc',
            'chf' => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
//			'chm'  => 'B,76a4fb,1,2,0|b,f4d4b2,0,1,0',
            'chdl' => $this->__('Number of items') . '|' . $this->__('Total Sales(%s)', $currency),
            'chco' => '2424ff,db4814',
            'chxt' => 'x,y,y,r,r',
            'chxlexpend' => '|2:||' . $this->__('# Items') . '|4:||' . $this->__('Total Sales(%s)', $currency)
        );

        $this->setHtmlId('report_brand_sales');
        parent::__construct();
        $this->setTemplate('shopbybrand/report/graph.phtml');
    }

    /**
     * Get chart url
     *
     * @param bool $directUrl
     * @return string
     */
    public function getChartUrl($directUrl = true) {
        $directUrl = true;
        $params = $this->_google_chart_params;
        $this->_allSeries = $this->getRowsData($this->_dataRows);

        foreach ($this->_axisMaps as $axis => $attr) {
            $this->setAxisLabels($axis, $this->getRowsData($attr, true));
        }

        $timezoneLocal = Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        $customStart = Mage::helper('shopbybrand')->getZendDate(Mage::app()->getRequest()->getParam('from'));
        $customEnd = Mage::helper('shopbybrand')->getZendDate(Mage::app()->getRequest()->getParam('to'));
        
        if ($customStart) {
            $customStart->setHour(0);
            $customStart->setMinute(0);
            $customStart->setSecond(0);
        }
        if ($customEnd) {
            $customEnd->setHour(23);
            $customEnd->setMinute(59);
            $customEnd->setSecond(59);
        }

        list ($dateStart, $dateEnd) = Mage::getResourceModel('shopbybrand/brand_collection')
                ->getDateRange($this->getDataHelper()->getParam('period'), $customStart, $customEnd, true);

        $tzDateStart = clone $dateStart;
//        $tzDateStart->setTimezone($timezoneLocal);

        $dates = array();
        $datas = array();
        while ($dateStart->compare($dateEnd) <= 0) {
            switch ($this->getDataHelper()->getParam('period')) {
                case '24h':
                    $d = $dateStart->toString('yyyy-MM-dd HH:00');
                    $dLabel = $tzDateStart->toString('yyyy-MM-dd HH:00');
                    $dateStart->addHour(1);
                    $tzDateStart->addHour(1);
                    break;
                case '7d':
                case '1m':
                    $d = $dateStart->toString('yyyy-MM-dd');
                    $dLabel = $tzDateStart->toString('yyyy-MM-dd');
                    $dateStart->addDay(1);
                    $tzDateStart->addDay(1);
                    break;
                case 'custom':
                    $rangeCustom = Mage::helper('shopbybrand')->getSubtractTime();
                    if ($rangeCustom == 'y') {
                        $d = $dateStart->toString('yyyy');
                        $dLabel = $dateStart->toString('yyyy'); 
                        $dateStart->addYear(1);
                    } elseif ($rangeCustom == 'm') {
                        $d = $dateStart->toString('yyyy-MM');
                        $dLabel = $dateStart->toString('yyyy-MM'); // $tzDateStart->toString('yyyy-MM');
                        $dateStart->addMonth(1);
                    } elseif ($rangeCustom == 'd') {
                        $d = $dateStart->toString('yyyy-MM-dd');
                        $dLabel = $tzDateStart->toString('yyyy-MM-dd');
                        $dateStart->addDay(1);
                        $tzDateStart->addDay(1);
                    } else {
                        $d = $dateStart->toString('yyyy-MM-dd HH:00');
                        $dLabel = $tzDateStart->toString('yyyy-MM-dd HH:00');
                        $dateStart->addHour(1);
                        $tzDateStart->addHour(1);
                    }
                    break;
                case '1y':
                case '2y':
                    $d = $dateStart->toString('yyyy-MM');
                    $dLabel = $dateStart->toString('yyyy-MM'); // $tzDateStart->toString('yyyy-MM');
                    $dateStart->addMonth(1);
                    // $tzDateStart->addMonth(1);
                    break;
            }
            foreach ($this->getAllSeries() as $index => $serie) {
                if (in_array($d, $this->_axisLabels['x'])) {
                    $datas[$index][] = (float) array_shift($this->_allSeries[$index]);
                } else {
                    $datas[$index][] = 0;
                }
            }
            $dates[] = $dLabel;
        }

        /**
         * setting skip step
         */
        if (count($dates) > 8 && count($dates) < 15) {
            $c = 1;
        } else if (count($dates) >= 15) {
            $c = 2;
        } else {
            $c = 0;
        }
        /**
         * skipping some x labels for good reading
         */
        $i = 0;
        foreach ($dates as $k => $d) {
            if ($i == $c) {
                $dates[$k] = $d;
                $i = 0;
            } else {
                $dates[$k] = '';
                $i++;
            }
        }

        $this->_axisLabels['x'] = $dates;
        $this->_allSeries = $datas;

        //Google encoding values
        if ($this->_encoding == "s") {
            // simple encoding
            $params['chd'] = "s:";
            $dataDelimiter = "";
            $dataSetdelimiter = ",";
            $dataMissing = "_";
        } else {
            // extended encoding
            $params['chd'] = "e:";
            $dataDelimiter = "";
            $dataSetdelimiter = ",";
            $dataMissing = "__";
        }

        // process each string in the array, and find the max length
        foreach ($this->getAllSeries() as $index => $serie) {
            $localmaxlength[$index] = sizeof($serie);
            $localmaxvalue[$index] = max($serie);
            $localminvalue[$index] = min($serie);
        }

        if (is_numeric($this->_max)) {
            $maxvalue = $this->_max;
        } else {
            $maxvalue = max($localmaxvalue);
        }
        if (is_numeric($this->_min)) {
            $minvalue = $this->_min;
        } else {
            $minvalue = min($localminvalue);
        }

        // default values
        $yrange = 0;
        $yLabels = array();
        $miny = 0;
        $maxy = 0;
        $yorigin = 0;

        $maxlength = max($localmaxlength);
        if ($minvalue >= 0 && $maxvalue >= 0) {
            $miny = 0;
            if ($maxvalue > 10) {
                $p = pow(10, $this->_getPow($maxvalue));
                $maxy = (ceil($maxvalue / $p)) * $p;
                $yLabels = range($miny, $maxy, $p);
            } else {
                $maxy = ceil($maxvalue + 1);
                $yLabels = range($miny, $maxy, 1);
            }
            $yrange = $maxy;
            $yorigin = 0;
        }

        $chartdata = array();

        foreach ($this->getAllSeries() as $index => $serie) {
            $thisdataarray = $serie;
            if ($this->_encoding == "s") {
                // SIMPLE ENCODING
                for ($j = 0; $j < sizeof($thisdataarray); $j++) {
                    $currentvalue = $thisdataarray[$j];
                    if (is_numeric($currentvalue)) {
                        $ylocation = round((strlen($this->_simpleEncoding) - 1) * ($yorigin + $currentvalue) / $yrange);
                        array_push($chartdata, substr($this->_simpleEncoding, $ylocation, 1) . $dataDelimiter);
                    } else {
                        array_push($chartdata, $dataMissing . $dataDelimiter);
                    }
                }
                // END SIMPLE ENCODING
            } else {
                // EXTENDED ENCODING
                for ($j = 0; $j < sizeof($thisdataarray); $j++) {
                    $currentvalue = $thisdataarray[$j];
                    if (is_numeric($currentvalue)) {
                        if ($yrange) {
                            $ylocation = (4095 * ($yorigin + $currentvalue) / $yrange);
                        } else {
                            $ylocation = 0;
                        }
                        $firstchar = floor($ylocation / 64);
                        $secondchar = $ylocation % 64;
                        $mappedchar = substr($this->_extendedEncoding, $firstchar, 1)
                                . substr($this->_extendedEncoding, $secondchar, 1);
                        array_push($chartdata, $mappedchar . $dataDelimiter);
                    } else {
                        array_push($chartdata, $dataMissing . $dataDelimiter);
                    }
                }
                // ============= END EXTENDED ENCODING =============
            }
            array_push($chartdata, $dataSetdelimiter);
        }
        $buffer = implode('', $chartdata);

        $buffer = rtrim($buffer, $dataSetdelimiter);
        $buffer = rtrim($buffer, $dataDelimiter);
        $buffer = str_replace(($dataDelimiter . $dataSetdelimiter), $dataSetdelimiter, $buffer);

        $params['chd'] .= $buffer;

        $labelBuffer = "";
        $valueBuffer = array();
        $rangeBuffer = "";

        if (sizeof($this->_axisLabels) > 0) {
            if (!isset($params['chxt']))
                $params['chxt'] = implode(',', array_keys($this->_axisLabels));
            $indexid = 0;
            foreach ($this->_axisLabels as $idx => $labels) {
                if ($idx == 'x') {
                    /**
                     * Format date
                     */
                    foreach ($this->_axisLabels[$idx] as $_index => $_label) {
                        if ($_label != '') {
                            switch ($this->getDataHelper()->getParam('period')) {
                                case '24h':
                                    $this->_axisLabels[$idx][$_index] = $this->formatTime(
                                            new Zend_Date($_label, 'yyyy-MM-dd HH:00'), 'short', false
                                    );
                                    break;
                                case '7d':
                                case '1m':
                                    $this->_axisLabels[$idx][$_index] = $this->formatDate(
                                            new Zend_Date($_label, 'yyyy-MM-dd')
                                    );
                                    break;
                                case 'custom':
                                    $rangeCustom = Mage::helper('shopbybrand')->getSubtractTime();
                                    if ($rangeCustom == 'y') {
                                        $formats = Mage::app()->getLocale()->getTranslationList('datetime');
                                        $format = isset($formats['yyMM']) ? $formats['yyMM'] : 'MM/yyyy';
                                        $format = str_replace(array("yyyy", "yy", "MM"), array("Y", "y", "m"), $format);
                                        $this->_axisLabels[$idx][$_index] = date($format, strtotime($_label));
                                    } elseif ($rangeCustom == 'm') {
                                        $formats = Mage::app()->getLocale()->getTranslationList('datetime');
                                        $format = isset($formats['yyMM']) ? $formats['yyMM'] : 'MM/yyyy';
                                        $format = str_replace(array("yyyy", "yy", "MM"), array("Y", "y", "m"), $format);
                                        $this->_axisLabels[$idx][$_index] = date($format, strtotime($_label));
                                    } elseif ($rangeCustom == 'd') {
                                        $this->_axisLabels[$idx][$_index] = $this->formatDate(
                                                new Zend_Date($_label, 'yyyy-MM-dd')
                                        );
                                    } else {
                                        $this->_axisLabels[$idx][$_index] = $this->formatTime(
                                                new Zend_Date($_label, 'yyyy-MM-dd HH:00'), 'short', false
                                        );
                                    }
                                    break;
                                case '1y':
                                case '2y':
                                    $formats = Mage::app()->getLocale()->getTranslationList('datetime');
                                    $format = isset($formats['yyMM']) ? $formats['yyMM'] : 'MM/yyyy';
                                    $format = str_replace(array("yyyy", "yy", "MM"), array("Y", "y", "m"), $format);
                                    $this->_axisLabels[$idx][$_index] = date($format, strtotime($_label));
                                    break;
                            }
                        } else {
                            $this->_axisLabels[$idx][$_index] = '';
                        }
                    }

                    $tmpstring = implode('|', $this->_axisLabels[$idx]);

                    $valueBuffer[] = $indexid . ":|" . $tmpstring;
                    if (sizeof($this->_axisLabels[$idx]) > 1) {
                        $deltaX = 100 / (sizeof($this->_axisLabels[$idx]) - 1);
                    } else {
                        $deltaX = 100;
                    }
                } else if ($idx == 'y') {
                    $valueBuffer[] = $indexid . ":|" . implode('|', $yLabels);
                    if (sizeof($yLabels) - 1) {
                        $deltaY = 100 / (sizeof($yLabels) - 1);
                    } else {
                        $deltaY = 100;
                    }
                    // setting range values for y axis
                    $rangeBuffer = $indexid . "," . $miny . "," . $maxy . "|";
                } else if ($idx == 'r') {
                    foreach ($yLabels as $key => $value) {
                        $yLabels[$key] = $value * Mage::registry('rate_chart_money');
                    }
                    $valueBuffer[] = "3:|" . implode('|', $yLabels);
                    if (sizeof($yLabels) - 1) {
                        $deltaY = 100 / (sizeof($yLabels) - 1);
                    } else {
                        $deltaY = 100;
                    }
                    // setting range values for y axis
                    $rangeBuffer = "3," . $miny . "," . $maxy * Mage::registry('rate_chart_money') . "|";
                }
                $indexid++;
            }
            $params['chxl'] = implode('|', $valueBuffer);
            if (isset($params['chxlexpend']))
                if ($params['chxlexpend'] == 'currency')
                    $params['chxl'] .= '|2:|||(' . Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol() . ')';
                else
                    $params['chxl'] .= $params['chxlexpend'];
        };

        // chart size
        $params['chs'] = $this->getWidth() . 'x' . $this->getHeight();

        if (isset($deltaX) && isset($deltaY)) {
            $params['chg'] = $deltaX . ',' . $deltaY . ',1,0';
        }
        // return the encoded data
        if ($directUrl) {
            $p = array();
            foreach ($params as $name => $value) {
                $p[] = $name . '=' . urlencode($value);
            }
            return self::API_URL . '?' . implode('&', $p);
        } else {
            $gaData = urlencode(base64_encode(serialize($params)));
            $gaHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
            $params = array('ga' => $gaData, 'h' => $gaHash);
            return $this->getUrl('*/*/tunnel', array('_query' => $params));
        }
    }

    /**
     * Prepare chart data
     *
     * @return void
     */
    protected function _prepareData() {
        $this->setDataHelperName('shopbybrand/report');
        $this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));

        $this->setDataRows(array('brand_qty_ordered', 'brand_base_row_total'));
        $this->_axisMaps = array(
            'x' => 'range',
            'y' => 'brand_base_row_total',
            'r' => 'brand_qty_ordered',
        );


        $availablePeriods = array_keys($this->helper('shopbybrand/dashboard')->getDatePeriods());
        $period = $this->getRequest()->getParam('period');

        $this->getDataHelper()->setParam('period', ($period && in_array($period, $availablePeriods)) ? $period : '7d'
        );
    }

    public function getDataLifetime() {
        $brand=Mage::getModel('shopbybrand/brand')->load($this->getRequest()->getParam('id'));
        $productIDs = explode(',', $brand->getData('product_ids'));
        $collection = Mage::getResourceModel('sales/order_item_collection')
                ->addFieldToFilter('product_id',array('in'=>$productIDs));
        if (version_compare(Mage::getVersion(), '1.4.1.1', '>=')) {
            $sfog = Mage::getModel('core/resource')->getTableName('sales_flat_order_grid');
        } else {
            $sfog = Mage::getModel('core/resource')->getTableName('sales_order');
        }
        $collection->getSelect()
                    ->join(array('sfog'=>$sfog),'main_table.order_id = sfog.entity_id AND sfog.status = "complete"');
        $store = Mage::app()->getRequest()->getParam('store');
        if ($store) {
            $collection->addFieldtoFilter('main_table.store_id', $store);
        }
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(array(
            'totals_money' => 'SUM(main_table.base_row_total)',
            'totals_qty' => 'SUM(main_table.qty_ordered)',
        ));
        if($collection->getSize())
        return $collection->getFirstItem()->getData();
        return array(
            'totals_money'=>0,
            'totals_qty'=>0
        );
    }

}