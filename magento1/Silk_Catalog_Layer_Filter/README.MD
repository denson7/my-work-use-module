##Catalog结构##

 **app/code/local/Silk/Catalog**
列表页面`filter`功能复写`Silk_Catalog_Model_Layer_Filter_Attribute`,`Silk_Catalog_Model_Layer_Filter_Item`,`Silk_Catalog_Model_Layer_Filter_Price`,`Silk_Catalog_Model_Resource_Layer_Filter_Attribute`.选择子项后，当前选项不消失。`price`复写`Silk_Catalog_Model_Layer_Filter_Price`中方法`getPriceRange()`,让`range`固定未100.