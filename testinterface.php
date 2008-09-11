<?php


require_once 'Etk/ComboButton.php';
require_once 'Etk/StatusBar.php';

$win           = new GtkWindow(Gtk::WINDOW_TOPLEVEL);
$nbk_editor    = new GtkNotebook();
$nbk_sidepanel = new GtkNotebook();
$vbox          = new GtkVBox();
$hpaned        = new GtkHPaned();
$statusbar     = new EtkStatusBar();
$cbo_tabstyle  = new EtkComboButton();
$cbo_encoding  = new EtkComboButton();
$lbl_cursor    = new GtkLabel('menu bar');
$menubar       = new GtkMenuBar();
$mnu_item_file = new GtkMenuItem('_File');
$mnu_file      = new GtkMenu();

$win->add($vbox);
$win->set_data('nbk_editor',  $nbk_editor);
$win->set_data('nbk_sidebar', $nbk_sidepanel);
$win->set_data('statusbar',   $statusbar);
$win->set_data('lbl_cursor',  $lbl_cursor);
$win->set_data('cbo_encoding',  $lbl_cursor);
$win->set_data('cbo_tabstyle',  $lbl_cursor);


$menubar->append($mnu_item_file);
$mnu_item_file->set_submenu($mnu_file);

$mnu_file->append(new GtkMenuItem('_Open'));
$mnu_file->append(new GtkMenuItem('_Save'));
$mnu_file->append(new GtkMenuItem('_Save as'));
$mnu_file->append(new GtkMenuItem('_Save all'));

$vbox->pack_start($menubar,false);
$vbox->pack_start($hpaned);
$vbox->pack_start($statusbar, false, false, 0);

$hpaned->add1($nbk_sidepanel);
$hpaned->add2($nbk_editor);

$nbk_editor->append_page(new GtkLabel('test'),new GtkLabel('test'));

$nbk_sidepanel->append_page(new GtkLabel('test'),new GtkLabel('test'));
$nbk_sidepanel->set_size_request(180, -1);

$cbo_encoding->append_text('utf-8');
$cbo_encoding->append_text('iso-8859-1');
$cbo_encoding->set_relief(Gtk::RELIEF_NONE);

$cbo_tabstyle->append_text('Tab (2)');
$cbo_tabstyle->append_text('Tab (3)');
$cbo_tabstyle->append_text('Tab (4)');
$cbo_tabstyle->append_text('Tab (8)');
$cbo_tabstyle->append_text('Softtab (2)');
$cbo_tabstyle->append_text('Softtab (3)');
$cbo_tabstyle->append_text('Softtab (4)');
$cbo_tabstyle->append_text('Softtab (8)');
$cbo_tabstyle->set_relief(Gtk::RELIEF_NONE);


$statusbar->pack_start($lbl_cursor, false);
$statusbar->pack_start(new GtkVSeparator(), false);
$statusbar->pack_start($cbo_tabstyle, false);
$statusbar->pack_start(new GtkVSeparator(), false);
$statusbar->pack_start($cbo_encoding, false);
$statusbar->pack_start(new GtkVSeparator(), false);

$label = $win->get_data('lbl_cursor');
$label->set_text("Line:  14  Column:  1024");




$win->connect_simple('destroy', array('Gtk', 'main_quit'));
$win->resize(700, 480);
$win->show_all();


//$child = $parent->get_child(); // a GtkLabel
//$parent->remove($child);
//$child->destroy();

//$child->unparent();
gtk::main();
