<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-10-20
 * Time: 下午1:50
 */
class Silk_Catalog_Model_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
    /**
     * fixed price range
     */
    const PRICE_RANGE_FIXED = 100;

    /**
     * Get data for build price filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if (Mage::app()->getStore()->getConfig(self::XML_PATH_RANGE_CALCULATION) == self::RANGE_CALCULATION_IMPROVED) {
            return $this->_getCalculatedItemsData();
        } elseif ($this->getInterval()) {
//            return array();
        }

        $range      = $this->getPriceRange();
        $dbRanges   = $this->getRangeItemCounts($range);
        $data       = array();

        if (true) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];

            foreach ($dbRanges as $index => $count) {
                $fromPrice = ($index == 1) ? '' : (($index - 1) * $range);
                $toPrice = ($index == $lastIndex) ? '' : ($index * $range);

                $data[] = array(
                    'label' => $this->_renderRangeLabel($fromPrice, $toPrice),
                    'value' => $fromPrice . '-' . $toPrice,
                    'count' => $count,
                );
            }
        }

        return $data;
    }

    /**
     * price range fixed for 100
     * rewrite Mage_Catalog_Model_Layer_Filter_Price  getPriceRange
     * @return float|mixed|number
     */
    public function getPriceRange()
    {
        $range = self::PRICE_RANGE_FIXED;
        return $range;
    }
}