<?php
/* Copyright (C) 2019 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!class_exists('SeedObject'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call or for session timeout on our module page
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}


class Pricelist extends SeedObject
{
	/** @var string $table_element Table name in SQL */
	public $table_element = 'pricelist';

	/** @var string $element Name of the element (tip for better integration in Dolibarr: this value should be the reflection of the class name with ucfirst() function) */
	public $element = 'pricelist';

	/** @var int $isextrafieldmanaged Enable the fictionalises of extrafields */
	public $isextrafieldmanaged = 1;

	/** @var int $ismultientitymanaged 0=No test on entity, 1=Test with field entity, 2=Test with link by societe */
	public $ismultientitymanaged = 1;

	/**
	 *  'type' is the field format.
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'default' is a default value for creation (can still be replaced by the global setup of default values)
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'position' is the sort order of field.
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 */

	public $fields = array(
		'entity' => array(
			'type' => 'integer',
			'enabled' => 1,
			'visible' => 0,
			'default' => 1,
			'notnull' => 1,
			'index' => 1,
			'position' => 10
		),
		'fk_product' => array(
			'type' => 'integer',
			'enabled' => 1,
			'visible' => 0,
			'position' => 20,
		),
		'price' => array(
			'type' => 'double',
			'enabled' => 1,
			'visible' => 1,
			'position' => 50
		),
		'reduction' => array(
			'type' => '	double',
			'enabled' => 1,
			'visible' => 1,
			'position' => 50
		),
		'reason' => array(
			'type' => 'text',
			'enabled' => 1,
			'visible' => 3,
			'position' => 30
		),
		'date_start' => array(
			'type' => 'datetime',
			'enabled' => 1,
			'visible' => 1,
			'position' => 40
		),
		'date_end' => array(
			'type' => 'datetime',
			'enabled' => 1,
			'visible' => 1,
			'position' => 50
		)
	);


	/**
	 * pricelist constructor.
	 * @param DoliDB $db Database connector
	 */
	public function __construct($db)
	{
		global $conf;

		parent::__construct($db);

		$this->init();

		$this->entity = $conf->entity;
	}

	public function setElements($rowid,$fk_product,$price = 0,$reduction = 0,$reason = '',$date_start,$date_end = '')
	{
		$this->rowid = $rowid;
		$this->fk_product = $fk_product;
		$this->price =$price;
		$this->reduction =$reduction;
		$this->reason =$reason;
		$this->date_start =$date_start;
		$this->date_end =$date_end;
	}

	/**
	 * @param User $user User object
	 * @return int
	 */
	public function save($user)
	{
		return $this->create($user);
	}

	/**
	 * @param User $user User object
	 * @return int
	 */
	public function delete(User &$user)
	{
		$this->deleteObjectLinked();

		unset($this->fk_element); // avoid conflict with standard Dolibarr comportment
		return parent::delete($user);
	}

	/**
	 * @param int $withpicto Add picto into link
	 * @param string $moreparams Add more parameters in the URL
	 * @return string
	 */
	public function getNomUrl($withpicto = 0, $moreparams = '')
	{
		global $langs;

		$result = '';
		$label = '<u>' . $langs->trans("Showpricelist") . '</u>';

		$linkclose = '" title="' . dol_escape_htmltag($label, 1) . '" class="classfortooltip">';
		$link = '<a href="' . dol_buildpath('/pricelist/card.php', 1) . '?id=' . $this->id . urlencode($moreparams) . $linkclose;

		$linkend = '</a>';

		$picto = 'generic';
//        $picto='pricelist@pricelist';

		if ($withpicto) $result .= ($link . img_object($label, $picto, 'class="classfortooltip"') . $linkend);
		if ($withpicto && $withpicto != 2) $result .= ' ';

		$result .= $link . $this->ref . $linkend;

		return $result;
	}

	public function getAllByProductId($productID){
		$sql = 'SELECT';
		$sql.= ' rowid,';
		$sql.= ' fk_product,';
		$sql.= ' price,';
		$sql.= ' reduction,';
		$sql.= ' reason,';
		$sql.= ' date_start,';
		$sql.= ' date_end';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element;
		$sql.= ' WHERE fk_product='.$productID;
		$sql.= ' AND entity='.getEntity('products');
		$sql.= ' ORDER BY date_start DESC';

		$TPricelist = array();

		$resql=$this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);
				if ($obj)
				{
					$TPricelist[$obj->rowid]['rowid'] = $obj->rowid;
					$TPricelist[$obj->rowid]['fk_product'] = $obj->fk_product;
					$TPricelist[$obj->rowid]['price'] = $obj->price;
					$TPricelist[$obj->rowid]['reduction'] = $obj->reduction;
					$TPricelist[$obj->rowid]['reason'] = $obj->reason;
					$TPricelist[$obj->rowid]['date_start'] = $obj->date_start;
					$TPricelist[$obj->rowid]['date_end'] = $obj->date_end;
				}
				$i++;
			}
		}
		return $TPricelist;
	}

	public function getLastDateProduct($fk_product)
	{
		$sql = 'SELECT';
		$sql.= ' rowid,';
		$sql.= ' date_start';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element;
		$sql.= ' WHERE fk_product='.$fk_product;
		$sql.= ' AND entity='.getEntity('products');
		$sql.= ' ORDER BY date_start DESC';
		$sql.= ' LIMIT 1';

		$res = array();

		$resql=$this->db->query($sql);
		if ($resql)
		{
			$obj = $this->db->fetch_object($resql);
			if ($obj)
			{
				$res['rowid'] = $obj->rowid;
				$res['date_start'] = $obj->date_start;
			}
		}
		return $res;
	}

	/**
	 * @param int $id Identifiant
	 * @param null $ref Ref
	 * @param int $withpicto Add picto into link
	 * @param string $moreparams Add more parameters in the URL
	 * @return string
	 */
	public static function getStaticNomUrl($id, $ref = null, $withpicto = 0, $moreparams = '')
	{
		global $db;

		$object = new pricelist($db);
		$object->fetch($id, false, $ref);

		return $object->getNomUrl($withpicto, $moreparams);
	}
}
