<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
try {
    $post = Mage::getModel('blog/post')->loadByIdentifier('Hello');

    $post->setData('identifier', 'must-have-magento-extensions');
    $post->setData('title', 'Must-have Magento Extensions');
    $post->setData('status', '1');

    $post->setData('created_time', Mage::getModel('core/date')->gmtDate());
    $post->setData('update_time', null);
    $post->setData('user', 'aheadWorks');
    $post->setData('update_user', 'aheadWorks');

    $post->setData('meta_keywords', 'Must-have Magento Extensions, aheadWorks');
    $post->setData('meta_description', 'Must-have Magento Extensions');


    $post->setData('post_content', '
<p><strong>aheadWorks</strong> is a dynamic market leading provider of Magento extensions, Magento templates and themes, and custom development services with a comprehensive portfolio of best-in-class solutions for eCommerce businesses.</p>
<p>We have launched numerous powerful extensions to the world&rsquo;s fastest growing eCommerce platform Magento and here are the TOP modules:</p>
<table style="border-style: none; padding-bottom: 20px;">
<tbody>
<tr>
<td style="border-style: none; padding-right: 10px;" align="center" valign="top"><a title="Advanced Search" href="http://ecommerce.aheadworks.com/magento-extensions/advanced-search.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><img src="http://media.aheadworks.com/catalog/Advanced_Search_120_169.png" alt="Advanced Search" /></a></td>
<td style="border-style: none;">
<p>The <strong><a href="http://ecommerce.aheadworks.com/magento-extensions/advanced-search.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post">Advanced Search  extension</a></strong> replaces native search with Sphinx, an external search engine. So search works much more faster in your store, server load is decreased, and results become more trustworthy.</p>
<p><strong><a href="http://ecommerce.aheadworks.com/magento-extensions/advanced-search.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post">Advanced Search</a></strong> is a perfect solution for search through Magento-based stores. This extension uses an external search engine Sphinx. If you have 10000 products in your store, <strong><a href="http://ecommerce.aheadworks.com/magento-extensions/advanced-search.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post">Advanced Search</a></strong> will help your customers to find those which are required. If you have 1000 posts in your blog, it\'s not a problem for <strong><a href="http://ecommerce.aheadworks.com/magento-extensions/advanced-search.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post">Advanced Search</a></strong> as well because it is <strong>100% compatible with Blog</strong>.</p>
</td>
</tr>
</tbody>
</table>
<table style="border-style: none; padding-bottom: 20px;">
<tbody>
<tr>
<td style="border-style: none;">
<p>In today\'s fiercely competitive business environment, a caring attitude towards customers can go a long way in creating a delightful experience for your customer. The <a href="http://ecommerce.aheadworks.com/magento-extensions/follow-up-email.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><strong>Follow Up Email Magento extension</strong></a> is an indispensable tool to help you stay in close touch with your customers.</p>
<p>With the <a href="http://ecommerce.aheadworks.com/magento-extensions/follow-up-email.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><strong>Follow Up Email</strong></a> extension, you can make the emails automatically sent to customers on any event &ndash; abandoned cart, order obtained certain status, customer added product to wishlist, etc. This indispensable tool will help you win more sales and always stay in close touch with your consumers.</p>
<p>Enjoy these irresistible benefits that powerful features of the <a href="http://ecommerce.aheadworks.com/magento-extensions/follow-up-email.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><strong>Follow Up Email module</strong></a> give to your ecommerce website.</p>
</td>
<td style="border-style: none; padding-left: 10px;" align="center" valign="top"><a href="http://ecommerce.aheadworks.com/magento-extensions/follow-up-email.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><img src="http://media.aheadworks.com/catalog/AW_Follow_Up_120.png" alt="Follow Up Email" /></a></td>
</tr>
</tbody>
</table>
<table style="border-style: none; padding-bottom: 20px;">
<tbody>
<tr>
<td style="border-style: none; padding-right: 10px;" align="center" valign="top"><a title="AJAX Cart Pro" href="http://ecommerce.aheadworks.com/magento-extensions/ajax-cart-pro.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><img src="http://media.aheadworks.com/catalog/AW_AJAX_Cart_Pro_120.png" alt="AJAX Cart Pro" /></a></td>
<td style="border-style: none;">
<p>The <strong><a href="http://ecommerce.aheadworks.com/magento-extensions/ajax-cart-pro.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post">AJAX Cart Pro extension</a></strong> allows customers to effortlessly add and remove products from their shopping cart without having to continuously click the "update" button. With each addition or deletion, only the shopping cart is refreshed. This immediate interaction allows users to continue shopping without waiting for pages to refresh. Best of all, it also works on the shopping cart page, enabling deletions and additions without page reloads.</p>
<p>With the <strong><a href="http://ecommerce.aheadworks.com/magento-extensions/ajax-cart-pro.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post">AJAX Cart Pro Magento extension</a></strong>, when adding configurable, downloadable, virtual or products with custom options to cart from the category page, your consumers are not redirected to the product page &ndash; they are offered to select item\'s options exactly from the pop-up confirmation dialog.</p>
</td>
</tr>
</tbody>
</table>
<table style="border-style: none; padding-bottom: 20px;">
<tbody>
<tr>
<td style="border-style: none;">
<p>To conduct business effectively and efficiently, it&rsquo;s important to analyze your current activities using up-to-date reports. The <strong><a href="http://ecommerce.aheadworks.com/magento-extensions/advanced-reports.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post">Advanced Reports Magento extension</a></strong> allows creating a complete picture of your business situation.</p>
<p>The <strong><a href="http://ecommerce.aheadworks.com/magento-extensions/advanced-reports.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post">Advanced Reports extension</a></strong> improves functionality of native Magento reports and creates a complete picture of your business situation. <strong><a href="http://ecommerce.aheadworks.com/magento-extensions/advanced-reports.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post">Advanced Reports</a></strong> is a perfect solution for all your reporting needs whether you want to display data from various charts, aggregate information by week or anything else.</p>
</td>
<td style="border-style: none; padding-left: 10px;" align="center" valign="top"><a title="Advanced Reports" href="http://ecommerce.aheadworks.com/magento-extensions/advanced-reports.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><img title="Advanced Reports" src="http://blog.aheadworks.com/wp-content/uploads/2011/10/AW_Advanced_Reports.png" alt="Advanced Reports" /></a></td>
</tr>
</tbody>
</table>
<table style="border-style: none; padding-bottom: 20px;">
<tbody>
<tr>
<td style="border-style: none; padding-left: 10px;" align="center" valign="top"><a title="Help Desk Ultimate" href="http://ecommerce.aheadworks.com/magento-extensions/help-desk-ultimate.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><img src="http://blog.aheadworks.com/wp-content/uploads/2011/09/AW_Help_Desk_Ultimate_120.png" alt="Help Desk Ultimate" /></a></td>
<td style="border-style: none;">
<p>The <a href="http://ecommerce.aheadworks.com/magento-extensions/help-desk-ultimate.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><strong>Help Desk Ultimate Magento extension</strong></a> is considered to be the best case tracking and resolution system among customer service solutions and the most downloaded module for Enterprise Edition at Magento Connect.</p>
<p><a href="http://ecommerce.aheadworks.com/magento-extensions/help-desk-ultimate.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><strong>Help Desk Ultimate</strong></a> is a turnkey solution for customer care and support. This Magento module has an efficient case tracking and resolution system indispensable for a successful e-commerce business.</p>
<p><a href="http://ecommerce.aheadworks.com/magento-extensions/help-desk-ultimate.html?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><strong>Help Desk Ultimate</strong></a> helps you raise your customers care and support level. No more lost emails, forgotten answers, and inquiries. Our module converts customer emails into tickets making your help desk to be a seamless experience.</p>
</td>
</tr>
</tbody>
</table>
<p><a href="http://ecommerce.aheadworks.com?utm_source=AW_Blog&amp;utm_medium=welcome_post&amp;utm_campaign=welcome_post"><img style="align: center;" src="http://media.aheadworks.com/catalog/mc_banner.png" alt="" width="685px" /></a></p>
');

    $cats = Mage::getModel('blog/cat')->getCollection();
    foreach ($cats as $cat) {
        if ($cat->getIdentifier() == 'news') {
            $post->setData('cats', array($cat->getId()));
            break;
        }
    }

    $post->save();
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();