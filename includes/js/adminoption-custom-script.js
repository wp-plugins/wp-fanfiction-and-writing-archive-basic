
function prefilljs(val){
    if(val==1){
        jQuery("#fe_fiction_page_title").val("Fan fiction");
        jQuery("#custom_dashboard").attr('checked','checked');
        jQuery("#hide_admin_menus").attr('checked','checked');
        jQuery("#fiction_scoring").attr('checked','checked');
        jQuery("#fe_fiction_fandom_label").val("Fandom");
        jQuery("#fe_fiction_fandom_label_p").val("Fandoms");
        jQuery("#fe_fiction_fandom_url").val("fandoms");
        jQuery("#fe_fiction_label").val("Fan Fiction");
        jQuery("#fe_fiction_position").val("left");
        jQuery("#fe_fiction_url").val("fanfiction");
        jQuery("#fe_fiction_posting_page_id").val("add-fanfiction");
    }else if(val==2){
        jQuery("#fe_fiction_page_title").val("Creative Writing ");
        jQuery("#custom_dashboard").attr('checked','checked');
        jQuery("#hide_admin_menus").attr('checked','checked');
        jQuery("#fiction_scoring").attr('checked','checked');
        jQuery("#fe_fiction_fandom_label").val("writing");
        jQuery("#fe_fiction_fandom_label_p").val("writings");
        jQuery("#fe_fiction_fandom_url").val("writings");
        jQuery("#fe_fiction_label").val("Creative Writing");
        jQuery("#fe_fiction_position").val("left");
        jQuery("#fe_fiction_url").val("creativewriting");
        jQuery("#fe_fiction_posting_page_id").val("add-creative-writing");
    }
}