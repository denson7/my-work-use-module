<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-19
 * Time: 上午9:44
 */
namespace Webkul\MultiSelect\Block;

/**
 * MultiSelect block.
 *
 * @author      Webkul Software
 */
class Multiselect extends \Magento\Framework\View\Element\Template
{

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }
}