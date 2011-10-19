<?php
/*
 * This file is part of MVD_GUI.
 *
 *  MVD_GUI is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  MVD_GUI is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with MVD_GUI.  If not, see <http://www.gnu.org/licenses/>.
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.module.helper');

/**
 * Load a module and render it
 * @param name full name of the module
 * @param local_params extra params that will become 
 * available to this instance of the module
 */
class LoadModule
{
	function getModule( $name, $local_params = null )
	{
		$module =& JModuleHelper::getModule( $name );
		if ( $module )
		{
			// ensure we have a modifiable instance
			$module->local_params = array();
			if ( $local_params )
			{
				foreach ( $local_params as $ind=>$val )
				{
					$module->local_params[$ind] = $val;
				}
			}
			$document = &JFactory::getDocument();
			// of type JDocumentRendererModule
			$renderer = $document->loadRenderer('module');
			return $renderer->render($module,$local_params);
		}
		else
			return "Warning: $name not found";
	}
}

