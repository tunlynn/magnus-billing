/**
 * Classe que define a lista de "Callerid"
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2021 MagnusBilling. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v3
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnussolution/magnusbilling7/issues
 * =======================================
 * Magnusbilling.org <info@magnussolution.com>
 * 19/09/2012
 */
Ext.define('MBilling.view.servers.List', {
    extend: 'Ext.ux.grid.Panel',
    alias: 'widget.serverslist',
    store: 'Servers',
    initComponent: function() {
        var me = this;
        me.viewConfig = {
            loadMask: false,
            emptyText: App.user.language == 'pt_BR' ? me.emptyTextBr : me.emptyTextEn
        };
        me.columns = [{
            header: t('ID'),
            dataIndex: 'id',
            flex: 1,
            hidden: true,
            hideable: App.user.isAdmin
        }, {
            header: t('Name'),
            dataIndex: 'name',
            flex: 4
        }, {
            header: t('Host'),
            dataIndex: 'host',
            flex: 4
        }, {
            header: t('Username'),
            dataIndex: 'username',
            flex: 4
        }, {
            header: t('Type'),
            dataIndex: 'type',
            comboRelated: 'booleancombo',
            flex: 2
        }, {
            header: t('Status'),
            dataIndex: 'status',
            renderer: Helper.Util.formatBooleanServers,
            comboRelated: 'booleancombo',
            flex: 2,
            filter: {
                type: 'list',
                options: [
                    [1, t('Active')],
                    [0, t('Inactive')],
                    [2, t('OffLine')]
                ]
            }
        }]
        me.callParent(arguments);
    },
    emptyTextEn: '<div style="margin-top:25px; text-align: center; font-size: 16px;"><h4>Did you know that MagnusSolution developed a C Application for Asterisk to replace AGI? It makes your server more powerful and efficient.</h4>With AGI your server would crash with 12 to 15 CPS. With our C application, your server will be able to handle about 40 CPS with only 2 cores and 4GB of RAM. It also can deal with situations where it receives more than 40 CPS, answering requests with a 603 error, therefore preventing a crash.<br>Two new settings are added to your MagnusBilling: you\'re now able to set the maximum global CPS and maximum CPS per user.<br><b>This purchase includes the application and its assisted install. It does not include technical support.</b> An assisted installation of OpenSips is also included if you use slaves.<br>The C Application only handles outbound calls - it does not change the way MagnusBilling handles other stuff.<b>The subscription for the application is paid monthly.</b><br><br>Watch this video that compares AGI with our C application. <a href=https://youtu.be/kdiPZpW8xfs target=_blank >https://youtu.be/kdiPZpW8xfs</a><br><br><br><b>USD 40,00/month</b>??????<a  target="_blank" href="https://magnussolution.com/services/high-availability/c-application.html?pay" class="sppb-btn  sppb-btn-primary sppb-btn-square sppb-btn-outline" style="float: center; margin-left:auto;margin-right:auto;">Buy Now</a></div>',
    emptyTextBr: '<div style="margin-top:25px; text-align: center; font-size: 16px;"><h4>Voc?? sabia que a equipe MagnusSolution desenvolveu uma aplica????o em C para o Asterisk, substituindo o AGI? A aplica????o torna seu servidor mais potente e eficiente</h4>Com AGI seu servidor ir?? perder a establidade e confiabilidade quando estiver em torno de 12 a 15 CPS<br><br>Com nossa aplica????o em C, seu servidor se tornar?? capaz de processar mais de 40 CPS com apenas 2 cores e 4GB de RAM. Tamb??m ser?? poss??vel controlar o recebimento de mais de 40 CPS, a fim de n??o aceitar todas as chamadas para evitar a desestabiliza????o do sistema.Duas novas configura????es s??o adicionadas ao seu MagnusBilling: voc?? agora consegue definir o CPS m??ximo do sistema e tamb??m o CPS m??ximo para cada usu??rio. <br><b>Esta compra inclui apenas a aplica????o e a sua instala????o.<br>Ela n??o inclui suporte t??cnico.</b><br><br><br>Uma instala????o do OpenSips est?? inclu??da no pre??o caso voc?? utilize slaves.<br>A aplica????o em C apenas processa chamadas de sa??da - n??o alterando a forma com que o MagnusBilling processa outras coisas<br><b>A assinatura pela aplica????o ?? paga mensalmente.</b><br><br>Assista o video que compara AGIR com nossa App em C. <a href=https://youtu.be/DOc6y1yjIAY target=_blank >https://youtu.be/DOc6y1yjIAY</a><br><br><br><b>R$ 152,00/m??s</b>??????<a  target="_blank" href="https://magnussolution.com/br/servicos/auto-desempenho/aplicacao-em-c.html?pay" class="sppb-btn  sppb-btn-primary sppb-btn-square sppb-btn-outline" style="float: center; margin-left:auto;margin-right:auto;">COMPRAR</a></div>'
});