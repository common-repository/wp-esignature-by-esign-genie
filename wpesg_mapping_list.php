<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class WPESG_Mapping_Item_List extends WP_List_Table {
	/** Class constructor */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'eSignature for WordPress Mapping List', 'sp' ), //singular name of the listed records
			'plural'   => __( 'eSignature for WordPress Mapping List', 'sp' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

		] );
	}
	public static function get_wpesg_mapping_list( $per_page = 5, $page_number = 1 ) {
	  	global $wpdb;
		$sql = "SELECT DISTINCT(EMS.id),EMS.mapping_name,EMS.wp_form_type as 'Form_Type',EMS.wp_form_name as 'From_Title',EMS.esg_template_name as 'Template_Name',EMS.created_at as 'Created' FROM `".$wpdb->prefix."wpesg_mapping_system` as EMS LEFT JOIN `".$wpdb->prefix."wpesg_fields_mapping` as EFM ON EMS.id = EFM.mapping_id Where EMS.status = 1 ORDER BY EMS.id DESC";
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
		$result = $wpdb->get_results($sql, 'ARRAY_A');
		return $result;	
	}

	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_wpesg_mapping_list( $id ) {
		global $wpdb;
		$wpdb->delete(
		"{$wpdb->prefix}wpesg_mapping_system",
		[ 'ID' => $id ],
		[ '%d' ]
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wpesg_mapping_system WHERE status=1";
		return $wpdb->get_var( $sql );
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
	  _e( 'No mapping avaliable.', 'sp' );
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {
		// create a nonce
		$delete_nonce = wp_create_nonce( 'sp_delete_wpesg_mapping_list' );
		$title = '<strong>' . $item['name'] . '</strong>';
	  	$actions = [
	    	'delete' => sprintf( '<a href="?page=%s&action=%s&wpesg_mapping_list=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce ),
 			'edit' => sprintf('<a href="?page=%s&action=%s& hotel=%s">Edit</a>',esc_attr($_REQUEST['page']),'edit',$item['id'])

	  	];
		return $title . $this->row_actions( $actions );
	}
	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
	    case 'mapping_name':
	    case 'Form_Type':
	    case 'From_Title':
	    case 'Template_Name':
	    case 'Created':
		return $item[ $column_name ];
	    default:
	      return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
	  return sprintf(
	    '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
	  );
	}

	/**
 	*  Associative array of columns
	*
	* @return array
	*/
	function get_columns() {
	  $columns = [
	    'cb'      => '<input type="checkbox" />',
	    'mapping_name'    => __( 'Mapping Name', 'sp' ),
	    'Form_Type' => __( 'Form Type', 'sp' ),
	    'From_Title'    => __( 'Form Name', 'sp' ),
	    'Template_Name'    => __( 'Template Name', 'sp' ),
	    'Created'    => __( 'Created', 'sp' ),

	  ];

	  return $columns;
	}

	function column_mapping_name($item) {
		$actions = array(
				'edit'      => sprintf('<a href="?page=%s&action=%s&itemId=%s">Edit</a>','wpesg-edit-mapping','edit',$item['id']),
				'delete'    => sprintf('<a href="?page=%s&action=%s&itemId=%s">Delete</a>',esc_attr($_REQUEST['page']),'delete',$item['id']),
			);
		return sprintf('%1$s %2$s', $item['mapping_name'], $this->row_actions($actions) );
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'mapping_name' => array( 'mapping_name', true ),
			'Form_Type' => array( 'Form_Type', false ),
			'From_Title' => array( 'From_Title', false ),
			'Template_Name' => array( 'Template_Name', false ),
			'Created' => array( 'Created', false )
		);
		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];
		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();
		/** Process bulk action */
		$this->process_bulk_action();
		$per_page     = $this->get_items_per_page( 'mapping_list_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();
		$this->set_pagination_args( [
		'total_items' => $total_items, //WE have to calculate the total number of items
		'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );
		$this->items = self::get_wpesg_mapping_list( $per_page, $current_page );
	}

	public function process_bulk_action() {
		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'sp_delete_wpesg_mapping_list' ) ) {
				$itemId = esc_sql($_GET["itemId"]);
				$action = sanitize_text_field($_GET["action"]);
				if(isset($itemId)){
					if($action =='edit'){}
					$this->edit_mapping($itemEdit);
				}   	
				$this->delete_mapping($itemId);
			//print_r($nonce); die('ssds');
			/*die( 'Go get a life script kiddies' );*/
			}else{
				self::delete_customer( absint( $_GET['wpesg_mapping_list'] ) );
				wp_redirect( esc_url( add_query_arg() ) );
				exit;
			}
		}
	  // If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		|| ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		){
			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_wpesg_mapping_list( $id );
			}
			wp_redirect( esc_url( add_query_arg() ) );
			exit;
		}
	}

	function delete_mapping($Ids){
        global $wpdb;
        $this->delete_mapping_system($Ids);
		$this->delete_fields_mapping($Ids);
		$data = $this->delete_party_require_param($Ids);
		return true;
    }
	
	function delete_mapping_system($Ids){
        global $wpdb;
        $tablename=$wpdb->prefix."wpesg_mapping_system"; //geting our table name with prefix
        $sql = "DELETE FROM $tablename WHERE id='$Ids'";
        return $result=$wpdb->query($sql );
    }
	function delete_fields_mapping($Ids){
        global $wpdb;
        $tablename=$wpdb->prefix."wpesg_fields_mapping"; //geting our table name with prefix
        $sql = "DELETE FROM $tablename WHERE mapping_id='$Ids'";
        return $result=$wpdb->query($sql );
    }
	function delete_party_require_param($Ids){
        global $wpdb;
        $tablename=$wpdb->prefix."wpesg_party_require_param"; //geting our table name with prefix
		$reasult = $wpdb->get_results("SELECT id FROM $tablename WHERE mapping_id=$Ids");
        foreach($reasult as $val){
			$this->delete_party_mapping($val->id);
		}		
        $sql = "DELETE FROM $tablename WHERE mapping_id='$Ids'";
        return $result=$wpdb->query($sql );
    }
	function delete_party_mapping($Ids){
        global $wpdb;
        $tablename=$wpdb->prefix."wpesg_party_mapping"; //geting our table name with prefix
        $sql = "DELETE FROM $tablename WHERE party_id='$Ids'";
        return $result=$wpdb->query($sql );
    }
}

$myListTable = new WPESG_Mapping_Item_List();
?>