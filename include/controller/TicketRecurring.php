<?php

namespace controller;

class TicketRecurring extends Controller {

    protected function getEntityClassName() {
        return 'model\TicketRecurring';
    }

    protected function getListColumns() {
        return array(
            array('field' => 'stock', 'headerText' => 'stock', 'sortable' => 'true'),
            array('field' => 'ticket', 'headerText' => 'Ticket', 'sortable' => 'true'),
            array('field' => 'subject', 'headerText' => 'Subject', 'sortable' => 'true'),
            array('field' => 'last_opened', 'headerText' => 'Last Ocurrence', 'sortable' => 'true'),
            array('field' => 'hr_interval', 'headerText' => 'Interval', 'sortable' => 'true'),
            array('field' => 'active', 'headerText' => 'Is Active?', 'sortable' => 'true')
        );
    }

    protected function getTitle($plural = true) {
        return $plural ? 'Recurring Tickets' : 'Recurring Ticket';
    }

    public function listTicketsJson() {
        $tickets = \model\stockTicket::getAllTickets();
        $items = array();
        foreach ($tickets as $ticket) {
            $items[] = array('number' => $ticket->getNumber().' - '.$ticket->getSubject(),
                'ticket_id' => $ticket->getId());
        }

        return json_encode($items);
    }

    public function liststockJson() {
        $stocks = \model\stockTicket::getAllstock();
        $items = array();
        foreach ($stocks as $stock) {
            $items[] = array('asset_id' => $stock->getAsset_Id(),
                'stock_id' => $stock->getId());
        }

        return json_encode($items);
    }

    public function viewByTicketAction($id = 0) {
        $args = array();

        if (isset($id) && $id > 0) {
            $ticket = \Ticket::lookup($id);
            if (isset($ticket)) {
                $entityClass = $this->getEntityClassName();
                $items = $entityClass::findByTicketId($id);
                $args['ticket'] = $ticket;
                $args['items'] = $items;
            }
        }

        $this->viewAction(-1,
                $args);
    }

    public function addByTicketAction($id = 0) {
        $args = array();
        if (isset($id) && $id > 0) {
            $ticket = Ticket::lookup($id);
        }
        $ticket_items = stock_Ticket::getAllTickets();
        $tickets = array();
        foreach ($ticket_items as $item) {
            $ti = array('id' => $item->getId(),
                'number' => $item->getNumber(),
                'subject' => $item->getSubject());
            $tickets[] = $ti;
        }

        $stock_items = stock_Ticket::getAllstock();
        $stocks = array();
        foreach ($stock_items as $item) {
            $ti = array('id' => $item->id,
                'asset_id' => $item->asset_id);
            $stocks[] = $ti;
        }

        $title = 'New Recurring Ticket';
        $args['ticket'] = $ticket;
        $args['tickets'] = $tickets;
        $args['stocks'] = $stocks;
        $args['title'] = $title;

        $template_name = $this->getAddTemplateName();
        $this->render($template_name,
                $args);
    }

    protected function getViewDirectory() {
        return 'recurring';
    }

    public function listAction() {
        $enabled = \model\stockConfig::findByKey('recurrance_enabled');
        if (isset($enabled) && $enabled == 'true') {
            parent::listAction();
        } else {
            $args = array();
            $args['title'] = $this->getTitle();
            $args['dt_columns'] = $this->getListColumns();
            $args['enabled'] = $this->checkEventScheduler();

            $template_name = 'listRecurringTemplate.html.twig';
            $this->render($template_name,
                    $args);
        }
    }

    protected function checkEventScheduler() {
        $retval = false;
        $sql = "show variables like '%event_scheduler%'";

        $res = db_query($sql);
        if ($res && ($num = db_num_rows($res))) {
            while ($row = db_fetch_array($res)) {
                $retval = $row['Value'] == 'ON';
            }
        }
        return $retval;
    }

    protected function createEvent() {

        $sql = 'DROP EVENT IF EXISTS `' . TABLE_PREFIX . 'stockCron`';
        db_query($sql);
        $sql = 'CREATE EVENT `' . TABLE_PREFIX . 'stockCron`
                ON SCHEDULE EVERY 1 HOUR
                DO
                CALL `' . TABLE_PREFIX . 'stockCronProc`()';

        $res = db_query($sql);
        if ($res) {
            \model\stockConfig::saveConfig('recurrance_enabled',
                    'true');
        }
        return $res;
    }

    public function enableEventsAction() {
        if ($this->createEvent()) {
            parent::listAction();
            return;
        }
        $args = array();
        $args['title'] = $this->getTitle();
        $template_name = 'listRecurringTemplateFail.html.twig';
        $this->render($template_name,
                $args);
    }

}
