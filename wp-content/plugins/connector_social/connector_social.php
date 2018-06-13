<?php
/**
 * @package Connector
 * @version 1.0
 */

/*
Plugin Name: Connector Shere
Plugin URI: http://connector.ie
Description: .
Author: Gustavo Fittipaldi - Connector.ie
Version: 1.0
Author URI: http://github.com/fittipaldi
*/

class Plg_Connector_Social
{
    public function install()
    {
        global $wpdb;

        $wpdb->query("
            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}connector_social` (
              `ID` INT(11) NOT NULL AUTO_INCREMENT,
              `nome` VARCHAR(255) NULL DEFAULT NULL,
              `email` VARCHAR(150) NULL DEFAULT NULL,
              `telefone` VARCHAR(50) NULL DEFAULT NULL,
              `empresa` VARCHAR(255) NULL DEFAULT NULL,
              `o_que_precisa` TEXT NULL DEFAULT NULL,
              `verba_disponivel` VARCHAR(255) NULL DEFAULT NULL,
              `data_cadastro` DATETIME NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE INDEX `id_UNIQUE` (`id` ASC))
            ENGINE = InnoDB
            COLLATE = utf8mb4_unicode_520_ci;
        ");
    }

    public function uninstall()
    {
        // sem acao
    }

    public function save_cadastro()
    {
        global $wpdb;

        if (!trim($_REQUEST['nome'])) {
            die('Informe seu Nome!');
        }

        if (filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL) === FALSE) {
            die('Informe um E-mail válido!');
        }

        //if ($itemId = (int)$_REQUEST['id']) {
        $o_que_precisa = implode(', ', $_REQUEST['o_que_precisa']);
        $wpdb->query("
                UPDATE
                  `{$wpdb->prefix}orcamentos`
                SET
                  `nome` = '{$_REQUEST['nome']}',
                  `email` = '{$_REQUEST['email']}',
                  `telefone` = '{$_REQUEST['telefone']}',
                  `empresa` = '{$_REQUEST['empresa']}',
                  `o_que_precisa` = '{$o_que_precisa}',
                  `verba_disponivel` = '{$_REQUEST['verba_disponivel']}'
                WHERE
                  `ID` = {$itemId}
            ");

        $body = "
Nome: {$_REQUEST['nome']}
E-mail: {$_REQUEST['email']}
Telefone: {$_REQUEST['telefone']}
Empresa: {$_REQUEST['empresa']}
O que precisa: {$o_que_precisa}
Verba Disponivel: {$_REQUEST['verba_disponivel']}
            ";

        wp_mail(get_option('orcamento_email_config'), 'Atratis - Oraçamento', $body);

        die(json_encode(array(
            'id' => 0,
            'msg' => 'Solicitação enviada!!! Obrigado por seu contato, em breve retornaremos.'
        )));
        /*
        } else {
            $wpdb->query("
              INSERT INTO
                `{$wpdb->prefix}orcamentos`
                ( `ID`, `nome`, `email`, `data_cadastro` )
              VALUE
                ( NULL, '{$_REQUEST['nome']}', '{$_REQUEST['email']}',  NOW())
            ");
            $id = (int)$wpdb->insert_id;
            die(json_encode(array(
                'id' => $id,
                'msg' => ''
            )));
        }
        */

    }

    public function cadastros()
    {
        require_once ABSPATH . 'wp-content/plugins/orcamento/list-orcamento.php';

        $myListTable = new WP_List_Table_Orcamento();

        if (isset($_REQUEST['action'])) {
            $myListTable->prepare_action($_REQUEST['action']);
        }

        echo '<div class="wrap"><h2>Cadastro de Orçamentos</h2>';

        if ($myListTable->mensagem) {
            echo '<div id="message" class="updated">';
            foreach ($myListTable->mensagem as $msg) {
                echo '<p>' . $msg . '</p>';
            }
            echo '</div>';
        }

        $myListTable->prepare_items();

        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="orcamento">';

        $myListTable->search_box('Pesquisa', 'search_id');
        $myListTable->display();

        echo '</form></div>';
        echo '<div class="clear"></div>';

    }

    public function config()
    {
        if ($_POST) {
            $msg = '';
            if (update_option('orcamento_email_config', $_POST['email'])) {
                $msg = 'Salvo com sucesso!';
            }
        }
        $email = get_option('orcamento_email_config');
        ?>
        <style type="text/css">
            .input label {
                width: 120px;
                float: left;
                margin-top: 4px;
                font-size: 15px;
                color: #298CBA;
            }

            .file label {
                width: 225px;
                float: left;
                font-size: 14px;
                color: #298CBA;
            }
        </style>
        <div class="wrap">

            <h2>Form Configuração</h2>

            <?php if ($msg): ?>
                <p><?php echo $msg ?></p>
            <?php endif ?>

            <form method="post">
                <div class="input">
                    <label for="email">Seu E-mail</label>
                    <input type="text" name="email" style="width: 500px;" id="email" value="<?php echo $email; ?>"/>
                </div>
                <br clear="all"/>

                <p class="submit">
                    <span id="inputsubmit">
                        <input id="save_slider" name="save_slider" type="submit" value="Salvar" class="button-primary"/>
                    </span>
                </p>
            </form>
        </div>
        <?php
    }

    public function form_register($p_atts)
    {
        $atts = shortcode_atts(array(
            'view' => 'default',
        ), $p_atts);

        $view = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'register' . $atts['view'] . '.php';
        if (is_file($view)) {
            require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'register' . $atts['view'] . '.php';
        } else {
            require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'register-default.php';
        }
    }

}

// Add action ajax save form cadastro de orcamento
add_action('wp_ajax_register_profile', array('Plg_Connector_Social', 'save_cadastro'));
add_action('wp_ajax_nopriv_register_profile', array('Plg_Connector_Social', 'save_cadastro'));

//Shortcode [connector_social_register]
add_shortcode('connector_social_register', array('Plg_Connector_Social', 'form_register'));

// Chama Metodo que cria Banco de dados
register_activation_hook(dirname(__FILE__) . DIRECTORY_SEPARATOR . basename(__FILE__), array('Plg_Connector_Social', 'install'));

// Chama Metodo que drop Banco de dados
register_deactivation_hook(dirname(__FILE__) . DIRECTORY_SEPARATOR . basename(__FILE__), array('Plg_Connector_Social', 'uninstall'));

// Menu admin configuração
add_action('admin_menu', 'menu_config_orcamento');
function menu_config_orcamento()
{
    add_menu_page(__('Connector Social'), __('Connector Social'), 'edit_plugins', 'connector', array('Plg_Connector_Social', 'cadastros'));
    add_submenu_page('connector', __('Config'), __('Config'), 'edit_plugins', 'connector_config', array('Plg_Connector_Social', 'config'));
}

/**
 * Class WP_Widget_Connector_Social
 * Widget de Orcamento
 */
class WP_Widget_Connector_Social extends WP_Widget
{
    public function __construct()
    {
        $this->WP_Widget(false, 'WP_Widget_Connector_Social');
    }

    public function form($instance)
    {
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('layout'); ?>">Layout:</label>
            <select name="<?php echo $this->get_field_name('layout'); ?>" id="<?php echo $this->get_field_id('layout'); ?>">
                <option <?php echo ($instance['layout'] == 'default') ? 'selected="selected"' : ''; ?> value="default">Padrao</option>
            </select>
        </p>
        <?php
    }

    public function widget($args, $instance)
    {
        switch (strtolower($instance['layout'])):
            default:
                ?>
                <div class="box-proposta row">
                    <div class="col-xs-12 col-sm-4 col-md-4">
                        <div class="box-texto">
                            <h2>SOLICITE SUA PROPOSTA</h2>

                            <!--<p id="etapa">Etapa 1 de 2</p>-->
                        </div>
                    </div>
                    <div class="box-form col-xs-12 col-sm-8 col-md-8">
                        <form class="ac-custom ac-checkbox ac-checkmark" autocomplete="off" id="form-orcamento" action="<?php echo admin_url('admin-ajax.php'); ?>">

                            <div class="box-o-inpt">
                                <input type="hidden" name="action" id="action" value="cadastrar_orcamento">
                                <input type="hidden" name="id" id="id" value="0">
                                <!--<label class="red"><span id="num-etapa">1</span>Nos diga seu nome e e-mail?</label>-->
                                <input type="text" name="nome" id="nome" placeholder="Seu Nome" required="true"/>
                                <input type="email" name="email" id="email" placeholder="Seu E-mail" required="true"/>
                            </div>
                            <div id="box-mais" style="display: none;">
                                <input type="text" name="telefone" id="telefone" placeholder="Telefone"/>
                                <input type="text" name="empresa" id="empresa" class="ipt-orcamento" placeholder="Qual a sua empresa?"/>

                                <h3>Do que você precisa?</h3>

                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="o_que_precisa" name="o_que_precisa[]" value="Site">Site
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="o_que_precisa" name="o_que_precisa[]" value="Loja Online">Loja Online
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="o_que_precisa" name="o_que_precisa[]" value="Campanha Online">Campanha Online
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="o_que_precisa" name="o_que_precisa[]" value="E-mail MKT">E-mail MKT
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="o_que_precisa" name="o_que_precisa[]" value="Google Adwords">Google Adwords
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="o_que_precisa" name="o_que_precisa[]" value="Gestão de redes sociais">Gestão de redes sociais
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="o_que_precisa" name="o_que_precisa[]" value="Prefiro que indiquem">Prefiro que indiquem
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="o_que_precisa" name="o_que_precisa[]" value="Inbound Marketing">Inbound Marketing
                                    </label>
                                </div>

                                <br clear="all"/>

                                <h3>Qual a verba disponível para o projeto?</h3>
                                <div class="dropp">
                                    <div class="dropp-header">
                                        <span class="dropp-header__title js-value">Selecione o valor</span>
                                        <a href="#" class="dropp-header__btn js-dropp-action"><i class="icon"></i></a>
                                    </div>
                                    <div class="dropp-body" id="verba_disponivel">
                                        <label for="verba_disponivel-1">de R$ 1.000 a R$ 3.000
                                            <input type="radio" id="verba_disponivel-1" name="verba_disponivel" value="de R$ 1.000 a R$ 3.000"/>
                                        </label>
                                        <label for="verba_disponivel-2">de R$ 3.000 a R$ 5.000
                                            <input type="radio" id="verba_disponivel-2" name="verba_disponivel" value="de R$ 3.000 a R$ 5.000"/>
                                        </label>
                                        <label for="verba_disponivel-3">de R$ 5.000 a R$ 10.000
                                            <input type="radio" id="verba_disponivel-3" name="verba_disponivel" value="de R$ 5.000 a R$ 10.000"/>
                                        </label>
                                        <label for="verba_disponivel-4">maior que R$ 10.000
                                            <input type="radio" id="verba_disponivel-4" name="verba_disponivel" value="maior que R$ 10.000"/>
                                        </label>
                                        <label for="verba_disponivel-5">ainda não foi definido
                                            <input type="radio" id="verba_disponivel-5" name="verba_disponivel" value="ainda não foi definido"/>
                                        </label>
                                    </div>
                                </div>

                            </div>
                            <input type="button" class="bt" id="pre-submit-btn" value="Continuar"/>
                            <input type="submit" class="bt" id="submit-btn" style="display: none;" value="Continuar"/>
                        </form>
                    </div>
                </div>

                <script type="text/javascript">
                    jQuery(function () {

                        jQuery('input[name=verba_disponivel]').on('click', function (event) {
                            jQuery('.dropp-header__title').html(jQuery(this).val());
                        });

                        jQuery('#pre-submit-btn').on('click', function (event) {
                            jQuery('#box-mais').show();
                            jQuery('#pre-submit-btn').hide();
                            jQuery('#submit-btn').show();
                        });

                        jQuery('#form-orcamento').on('submit', function () {

                            if (jQuery('#box-mais').is(':visible')) {
                                var msg = '';
                                if (!jQuery('#telefone').val()) {
                                    msg += 'Campo de Telefone';
                                }
                                var msg_o_que_precisa = 'O que você precisa';
                                jQuery.each(jQuery('.o_que_precisa'), function (key, item) {
                                    if (!jQuery(item).is(':checked')) {
                                        msg_o_que_precisa = '';
                                    }
                                });
                                if (msg_o_que_precisa) {
                                    if (msg) {
                                        msg += ', ' + msg_o_que_precisa;
                                    } else {
                                        msg += msg_o_que_precisa;
                                    }
                                }
                                var msg_verba_disponivel = 'Qual a verba disponível para o projeto';
                                jQuery.each(jQuery('input[name=verba_disponivel]'), function (key, item) {
                                    if (jQuery(item).is(':checked')) {
                                        msg_verba_disponivel = '';
                                    }
                                });
                                if (msg_verba_disponivel) {
                                    if (msg) {
                                        msg += ' e ' + msg_verba_disponivel;
                                    } else {
                                        msg += msg_verba_disponivel;
                                    }
                                }
                                if (msg) {
                                    alert('Por favor, preencha os campos obrigatório(s): ' + msg);
                                    return false;
                                }
                                var action = jQuery(this).attr('action');
                                var method = jQuery(this).attr('method');
                                var data = jQuery(this).serialize();
                                jQuery.ajax({
                                    url: action,
                                    data: data,
                                    type: method,
                                    beforeSend: function (jqXHR, settings) {
                                    },
                                    success: function (data, textStatus, jqXHR) {
                                        data = eval('(' + data + ')');
                                        if (data.id) {
                                            jQuery('#etapa').html('Etapa 2 de 2');
                                            jQuery('#num-etapa').html('2');
                                            jQuery('#id').val(data.id);
                                        } else {
                                            jQuery('input[type=text], input[type=email], textarea').val('');
                                            jQuery('#id').val('0');
                                            jQuery('#etapa').html('Etapa 1 de 2');
                                            jQuery('#num-etapa').html('1');
                                            jQuery('#box-mais').hide();
                                            alert(data.msg);
                                        }
                                    }
                                });

                                return false;
                            }
                        });
                    });
                </script>

                <?php
                break;
        endswitch;
    }
}

function wgt_connector_social_init()
{
    register_widget('WP_Widget_Connector_Social');
}

add_action('widgets_init', 'wgt_connector_social_init');