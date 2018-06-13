<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class WP_List_Connector_Social extends WP_List_Table
{
    public $example_data = array();
    public $mensagem = array();

    public function __construct()
    {
        global $status, $page, $wp_query;

        parent::__construct(array(
            'singular' => __('Cadastro de Orçamento', 'orcamento'), //singular name of the listed records
            'plural' => __('Cadastro de Orçamentos', 'orcamento'), //plural name of the listed records
            'ajax' => true //does this table support ajax?
        ));

        if (isset($_GET['s']) && $_GET['s']) {
            $search = 'WHERE nome LIKE ("%' . $_GET['s'] . '%") OR  email LIKE("%' . $_GET['s'] . '%")';
            $this->example_data = $wp_query->get_results('SELECT * FROM ' . $wp_query->prefix . 'orcamentos ' . $search . ' ORDER BY id DESC', ARRAY_A);
        } else {
            $this->example_data = $wp_query->get_results('SELECT * FROM ' . $wp_query->prefix . 'orcamentos ORDER BY id DESC', ARRAY_A);
        }
        add_action('admin_head', array(&$this, 'admin_header'));
    }

    public function prepare_action($action)
    {
        global $wp_query;
        switch ($action) {
            case 'delete':
                if (is_array($_REQUEST['item'])) {
                    $wp_query->query('DELETE FROM ' . $wp_query->prefix . 'orcamentos WHERE ID IN (' . implode(', ', $_REQUEST['item']) . ')');
                } else if ($_REQUEST['item']) {
                    $wp_query->query('DELETE FROM ' . $wp_query->prefix . 'orcamentos WHERE ID = ' . (int)$_REQUEST['item']);
                }
                $this->example_data = $wp_query->get_results('SELECT * FROM ' . $wp_query->prefix . 'orcamentos ORDER BY id DESC', ARRAY_A);;
                $this->mensagem[] = 'Deletado com sucesso!';
                break;
            case 'edit':
                $item = $wp_query->get_row('SELECT * FROM ' . $wp_query->prefix . 'orcamentos WHERE ID = ' . (int)$_REQUEST['item'], OBJECT);
                require ABSPATH . 'wp-content/plugins/orcamento/html/from-edit.php';
                break;
            case 'save':
                $itemId = (int)$_POST['ID'];
                if ($itemId) {
                    $wp_query->query("
                        UPDATE
                          `{$wp_query->prefix}orcamentos`
                        SET
                          `nome` = '{$_POST['nome']}',
                          `email` = '{$_POST['email']}',
                          `telefone` = '{$_POST['telefone']}',
                          `empresa` = '{$_POST['empresa']}',
                          `verba_disponivel` = '{$_POST['verba_disponivel']}',
                          `o_que_precisa` = '{$_POST['o_que_precisa']}'
                        WHERE
                          `ID` = {$itemId}
                    ");
                    $this->mensagem[] = 'Atualizado com sucesso!';
                }
                $this->example_data = $wp_query->get_results('SELECT * FROM ' . $wp_query->prefix . 'orcamentos ORDER BY id DESC', ARRAY_A);
                break;
            default:
                break;
        }
    }

    public function admin_header()
    {
        $page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;
        if ('orcamento' != $page)
            return;
        echo '<style type="text / css">';
        echo '.wp-list-table .column-id { width: 5%; }';
        echo '.wp-list-table .column-nome { width: 40%; }';
        echo '.wp-list-table .column-data_cadastro { width: 35%; }';
        echo '.wp-list-table .column-ID { width: 20%;}';
        echo '</style>';
    }

    public function no_items()
    {
        _e('Sem item cadastrado!');
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'nome':
            case 'email':
            case 'data_cadastro':
            case 'ID':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'nome' => array('nome', true),
            'email' => array('email', false),
            'date' => array('data_cadastro', false),
            'ID' => array('ID', false)
        );
        return $sortable_columns;
    }

    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'nome' => __('Nome', 'orcamento'),
            'email' => __('E-mail', 'orcamento'),
            'data_cadastro' => __('Data de cadastro', 'orcamento'),
            'ID' => __('ID', 'orcamento')
        );
        return $columns;
    }

    public function usort_reorder($a, $b)
    {
        // If no sort, default to title
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'nome';
        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    public function column_nome($item)
    {
        $actions = array(
            'edit' => sprintf('<a href="?page=%s&action=%s&item=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['ID']),
            'delete' => sprintf('<a href="?page=%s&action=%s&item=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['ID']),
        );

        return sprintf('%1$s %2$s', $item['nome'], $this->row_actions($actions));
    }

    public function column_views($item)
    {
        return intval($item['views']);
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="item[]" value="%s" />', $item['ID']
        );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        usort($this->example_data, array(&$this, 'usort_reorder'));

        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($this->example_data);

        // only ncessary because we have sample data
        $this->found_data = array_slice($this->example_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page //WE have to determine how many items to show on a page
        ));
        $this->items = $this->found_data;
    }

} //class