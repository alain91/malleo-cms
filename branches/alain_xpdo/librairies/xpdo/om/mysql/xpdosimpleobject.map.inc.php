<?php
/*
 * Copyright 2006, 2007, 2008, 2009 by  Jason Coward <xpdo@opengeek.com>
 * 
 * This file is part of xPDO.
 *
 * xPDO is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * xPDO is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * xPDO; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * Metadata map for the xPDOSimpleObject class.
 * 
 * Provides an integer primary key column which uses MySQL's native 
 * auto_increment primary key generation facilities.
 * 
 * @see xPDOSimpleObject
 * @package xpdo
 * @subpackage om.mysql
 */
$xpdo_meta_map['xPDOSimpleObject']['table']= null;
$xpdo_meta_map['xPDOSimpleObject']['fields']= array (
   'id' => null,
);
$xpdo_meta_map['xPDOSimpleObject']['fieldMeta']= array (
   'id' => array('dbtype' => 'INTEGER', 'phptype' => 'integer', 'null' => false, 'index' => 'pk', 'generated' => 'native', 'attributes' => 'unsigned', ),
);
$xpdo_meta_map['xpdosimpleobject']= & $xpdo_meta_map['xPDOSimpleObject'];