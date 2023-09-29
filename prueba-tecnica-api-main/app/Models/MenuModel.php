<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MenuModel extends Model {

    public static function getMenu($userId) {
        // OBTENER MENU SEGUN LOS PERMISOS DEL USUARIO
        $sql = "SELECT 
                    m.menu_name as menu_parent,
                    m.menu_type as menu_parent_type,
                    m.menu_icon as menu_icon,
                    m.menu_icon_library as menu_icon_library,
                    m2.menu_name as menu_children,
                    m2.menu_type as menu_children_type,
                    m2.menu_action as menu_children_action,
                    pm2.profilemenu_permissions as permissions
                from 
                    menus m
                    inner join profiles_menu pm
                        on pm.menu_id = m.menu_id
                        and pm.profilemenu_active = 1
                    inner join users_profiles up 
                        on up.profile_id = pm.profile_id
                        and up.userprofile_active = 1
                        and up.user_id = ?
                    left join menus m2
                        on m2.menu_parent = m.menu_id
                        and m.menu_active = 1
                        and m2.menu_active = 1
                    inner join profiles_menu pm2
                        on pm2.menu_id = m2.menu_id
                        and pm2.profilemenu_active = 1
                    inner join users_profiles up2
                        on up2.profile_id = pm2.profile_id
                        and up2.userprofile_active = 1
                        and up2.user_id = ?
                where m.menu_parent is null 
                group by 
                    m.menu_parent,
                    m.menu_name,
                    m2.menu_name,
                    m.menu_type,
                    m.menu_icon,
                    m.menu_icon_library,
                    m2.menu_type,
                    m2.menu_action,
                    pm2.profilemenu_permissions,
                    m.menu_order,
                    m2.menu_order
                order by m.menu_order, m2.menu_order";
        return DB::select($sql, array($userId, $userId));
    }
}
