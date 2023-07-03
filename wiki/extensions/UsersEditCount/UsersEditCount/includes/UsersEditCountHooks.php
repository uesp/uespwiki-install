<?php
class UsersEditCountHooks
{
    public static function onwgQueryPages(&$wgQueryPages)
    {
        $wgQueryPages[] = ['SpecialUsersEditCount', 'Userseditcount'];
    }
}
