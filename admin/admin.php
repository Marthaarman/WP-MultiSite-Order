<?php

add_action('network_admin_menu', 'multisite_order_admin_menu', 11, 0);

function multisite_order_admin_menu() {
    add_menu_page( 
        'Multisite Order',
        'Multisite Order',
        'multisite_order',
        'multisite_order_admin', 
        'multisite_order_admin'
    );
}

function multisite_order_admin_save() {
    if(isset($_POST['save'])) {
        $multisite_order = Multisite_Order::get_instance();
        $sites = $multisite_order->get_ordered_sites();
        foreach($sites as $site) {
            $order = $_POST['order_'.$site->blog_id];
            $multisite_order->set_site_order($site, $order);
        }
    }
}

function multisite_order_admin() {
    multisite_order_admin_save();
    $multisite_order = Multisite_Order::get_instance();
    $sites = $multisite_order->get_ordered_sites();
    echo "<form method='post' action=''>";
    echo "<table class='widefat fixed'>";
    echo "
        <thead>
            <td>Site / Blog ID</td>
            <td>Site / Blog</td>
            <td>Order</td>
        </thead>
    ";
    foreach($sites as $i => $site) {
        $class = $i%2 === 0 ? 'alternate' : '';
        echo "
            <tr class='{$class}'>
                <td>{$site->blog_id}</td>
                <td>".(get_blog_option($site->blog_id, 'blogname'))."</td>
                <td>
                    <input type='number' step='1' value='".($i + 1)."' placeholder='{$i}' name='order_{$site->blog_id}' />
                </td>
            </tr>
        ";
    }
    echo "
        <tr class='".($i %2 === 0 ? '' : 'alternate')."'>
            <td></td>
            <td></td>
            <td><input type='submit' name='save' value='Save' class='button submit' /></td>
        </tr>
    ";
    echo "</table>";
    echo "</form>";
}