<?php

class FrontendifyHTMLEditorField extends HtmlEditorField {

    public function __construct($name, $title = null, $value = '', $class) {

        parent::__construct($name, $title, $value);

        Requirements::customScript('
        var ssTinyMceConfig'.$class.' = {"friendly_name":"(Please set a friendly name for this config)","priority":0,"mode":"specific_textareas","editor_selector":"htmleditor","width":"100%","auto_resize":false,"theme":"advanced","theme_advanced_layout_manager":"SimpleLayout","theme_advanced_toolbar_location":"top","theme_advanced_toolbar_align":"left","theme_advanced_toolbar_parent":"right","blockquote_clear_tag":"p","table_inline_editing":true,"safari_warning":false,"relative_urls":true,"verify_html":true,"browser_spellcheck":true,"plugins":"contextmenu,table,emotions,paste","theme_advanced_buttons1":"bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,formatselect,separator,bullist,numlist,outdent,indent,blockquote,hr,charmap","theme_advanced_buttons2":"undo,redo,separator,cut,copy,paste,pastetext,pasteword,separator,advcode,search,replace,selectall,visualaid,separator,tablecontrols","theme_advanced_buttons3":""};
        ');

        Requirements::customScript("
        ssTinyMceConfig".$class.".theme_advanced_buttons1='bold,italic,underline,link,unlink';
        ssTinyMceConfig".$class.".theme_advanced_buttons2='';
        ssTinyMceConfig".$class.".mode = 'specific_textareas';
        ssTinyMceConfig".$class.".plugins = 'fullpage';
        ssTinyMceConfig".$class.".forced_root_block = '';
        ssTinyMceConfig".$class.".theme_advanced_path = false;
        ssTinyMceConfig".$class.".editor_selector='".$class."';tinyMCE.init(ssTinyMceConfig".$class.");");

    }

}