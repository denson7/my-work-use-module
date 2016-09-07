function charFilter(el){
        if(curentFilter)
            curentFilter.removeClassName('isActiveCate');
        el.addClassName('isActiveCate');
        curentFilter = el;
        brandFilter();
    }
    function brandFilter(){
        var cat = '';
        var char = '';
        if(curentActiveCate)
            cat = '.c'+curentActiveCate.readAttribute('cateId');
        if(curentFilter)
            char = '.'+curentFilter.readAttribute('group');
        $$('li.list_1').each(function(el){
            el.hide();
        });
        $$('li.list_1'+cat+char).each(function(el){
            el.show();
        });
        if($$('.brandslist'))
            $$('.brandslist').each(function(el){
                if(!$$('.brandslist#' +el.id +' li.list_1'+cat+char).length)
                    el.hide();
                else
                    el.show();
            });
    }
    function cateFilter(el){
            if(curentActiveCate)
                curentActiveCate.removeClassName('isActiveCate');
            el.addClassName('isActiveCate');
            curentActiveCate = el;
            brandFilter();
        }
        function showChild(el){
            var showChild = el.next('ul')
            if(el.hasClassName('child_active')){
                    el.removeClassName('child_active');
                    showChild.hide();
                }
                else{
                    el.addClassName('child_active');
                    showChild.show();
                }
        }

