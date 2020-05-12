<?php
namespace App\Controller;

use App\Controller\AppControler;
use Cake\I18n\Time;

class MessagesController extends AppController {

    public function index() {
        if ($this->request->is('post')) {
            $data = $this->request->data['Messages'];
            $entity = $this->Messages->newEntity($data);
            $entity->create_at = new Time(date('Y-m-d H:i:s'));
            $this->Messages->save($entity);
        } else {
            $entity = $this->Messages->newEntity();
        }
        $data = $this->Messages->find('all')
                                ->contain(['People'])
                                ->order(['create_at' => 'desc']);
        $this->set('data', $data);
        $this->set('entity', $entity);
    }
}
?>