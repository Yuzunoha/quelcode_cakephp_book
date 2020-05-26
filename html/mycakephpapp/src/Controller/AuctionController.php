<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Event\Event;
use Exception;

class AuctionController extends AuctionBaseController {
    
    // デフォルトテーブル使わない
    public $useTable = false;

    // 初期化処理
    public function initialize() {

        parent::initialize();
        $this->loadComponent('Paginator');

        // 必要なモデルをすべてロード
        $this->loadModel('Users');
        $this->loadModel('Biditems');
        $this->loadModel('Bidrequests');
        $this->loadModel('Bidinfo');
        $this->loadModel('Bidmessages');

        // ログインしているユーザー情報をauthuserに設定
        $this->set('authuser', $this->Auth->user());

        // レイアウトをauctionに変更
        $this->viewBuilder()->setlayout('auction');

    }

    // トップページ
    public function index() {
        // ページネーションでBiditemsを取得
        $auction = $this->paginate('Biditems', [
            'order' => ['endtime' => 'desc'],
            'limit' => 10
        ]);
        $this->set(compact('auction'));
    }

    // 商品情報の表示
    public function view($id = null) {
        // $idのBiditemを取得
        $biditem = $this->Biditems->get($id, [
            'contain' => ['Users', 'Bidinfo', 'Bidinfo.Users']
        ]);

        // オークション終了時の処理
        if ($bidtime->endtime < new \DateTime('now') and $biditem->finished == 0) {
            // finishedを1に変更して保存
            $biditem->finished = 1;
            $this->Biditems->save($biditem);
            // Bidinfoを作成する
            $bidinfo = $this->Bidinfo->newEntity();
            // Bidinfoのbiditem_idに$idを設定
            $bidinfo->biditem_id = $id;
            // 最高金額のBidrequestを検索
            $bidrequest = $this->Bidrequeests->find('all', [
                'conditions' => ['biditem_id' => $id],
                'contain' => ['Users'],
                'order' => ['price' => 'desc']
            ])->first();
            // Bidrequestが得られた時の処理
            if (!empty($bidrequest)) {
                // Bidinfoの各種プロパティを設定して保存する
                $bidinfo->user_id = $bidrequest->user->id;
                $bidinfo->user = $bidrequest->user;
                $bidinfo->price = $bidrequest->price;
                $this->Bidinfo->save($bidinfo);
            }
            // Biditemのbidinfoに$bidinfoを設定
            $biditem->bidinfo = $bidinfo;
        }
        // Bidrequestsからbiditem_idが$idのものを取得
        $bidrequests = $this->Bidrequests->find('all', [
            'conditions' => ['biditem_id' => $id],
            'contain' => ['Users'],
            'order' => ['price' => 'desc']
        ])->toArray();
        // オブジェクト類をテンプレート用に設定
        $this->set(compact('biditem', 'bidrequests'));
    }

    // 出品する処理
    public function add() {
        // Biditemインスタンスを用意
        $biditem = $this->Biditems->newEntity();
        // Post時の処理
        if ($this->request->isPost()) {
            // $biditemにフォームの送信内容を設定
            $biditem = $this->Biditems->patchEntity($biditem, $this->request->getData());
            // $biditemを保存する
            if ($this->Biditems->save($biditem)) {
                // 成功時のメッセージ
                $this->Flash->success(__('保存しました。'));
                // トップページ(index)に移動
                return $this->redirect(['action' => 'index']);
            }
            // 失敗時のメッセージ
            $this->Flash->error(__('保存に失敗しました。もう一度入力下さい。'));
        }
        // 値を保存
        $this->set(compact('biditem'));
    }

    // 入札の処理
    public function bid($biditem_id = null) {
        // 入札用のBidrequestインスタンスを用意
        $bidrequest = $this->Bidreuqest->newEntity();
        // $bidrequestにbiditem_idとuser_idを設定
        $bidrequest->biditem_id = $biditem_id;
        $bidrequest->user_id = $this->Auth->user('id');
        // Post送信時の処理
        if ($this->request->isPost()) {
            // $bidrequestに送信フォームの内容を反映する
            $bidrequest = $this->Bidrequests->patchEntity($bidrequest, $this->request->getData());
            // Bidrequestを保存
            if ($this->Bidrequests->save($bidrequest)) {
                // 成功時のメッセージ
                $this->Flash->success(__('入札を送信しました。'));
                // トップページにリダイレクト
                return $this->redirect(['action' => 'view', $biditem_id]);
            }
            // 失敗時のメッセージ
            $this->Flash->error(__('入札に失敗しました。もう一度入力ください。'));
        }
        // $biditem_idの$biditemを取得する
        $biditem = $this->Biditems->get($biditem_id);
        $this->set(compact('bidrequest', 'biditem'));

    }

    // 落札者とのメッセージ
    public function msg($bidinfo_id = null) {
        // Bidmessageを新たに用意
        $bidmsg = $this->Bidmessages->newEntity();
        // Post送信時の処理
        if ($this->request->isPost()) {
            // 送信されたフォームで$bidmsgを更新
            $bidmsg = $this->Bidmessages->patchEntity($bidmsg, $this->request->getData());
            // Bidmessageを保存
            if ($this->Bidmessages->save($bidmsg)) {
                // 成功時のメッセージ
                $this->Flash->success(__('保存しました。'));
            } else {
                $this->Flash->error(__('保存に失敗しました。もう一度入力下さい。'));
            }
        }
        try {
            // $bidinfo_idからBidinfoを取得する
            $bidinfo = $this->Bidinfo->get($bidinfo_id, ['contain' => ['Biditems']]);
        } catch(Exception $e) {
            $bidinfo = null;
        }
        // Bidmessageをbidinfo_idとuser_idで検索
        $bidmsgs = $this->Bidmessages->find('all', [
            'conditions' => ['bidinfo_id' => $bidinfo_id],
            'contain' => ['Users'],
            'order' => ['created' => 'desc']
        ]);
        $this->set(compact('bidmsgs', 'bidinfo', 'bidmsg'));
    }

    // 落札情報の表示
    public function home() {
        // 自分が落札したBidinfoをページネーションで取得
        $bidinfo = $this->paginate('Bidinfo', [
            'conditions' => ['Bidinfo.user_id' => $this->Auth->user('id')],
            'contain' => ['Users', 'Biditems'],
            'order' => ['created' => 'desc'],
            'limit' => 10
        ])->toArray();
        $this->set(compact('bidinfo'));
    }

    // 種品情報の表示
    public function home2() {
        // 自分が出品したBiditemをページネーションで取得
        $biditems = $this->paginate('Biditem', [
            'conditions' => ['Biditem.user_id' => $this->Auth->user('id')],
            'contain' => ['Users', 'Bidinfo'],
            'order' => ['created' => 'desc'],
            'limit' => 10
        ])->toArray();
        $this->set(compact('biditems'));
    }
}
?>