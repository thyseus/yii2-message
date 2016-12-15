<?php

namespace thyseus\message\controllers;

use thyseus\message\models\IgnoreListEntry;
use thyseus\message\models\Message;
use thyseus\message\models\MessageSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['inbox', 'ignorelist', 'sent', 'compose', 'view', 'delete', 'mark-all-as-read'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Message models where i am the recipient.
     * @return mixed
     */
    public function actionInbox()
    {
        $searchModel = new MessageSearch();
        $searchModel->to = Yii::$app->user->id;
        $searchModel->inbox = true;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $users = ArrayHelper::map(
            Message::find()->where(['to' => Yii::$app->user->id])->groupBy('from')->all(), 'from', 'sender.username');

        return $this->render('inbox', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'users' => $users,
        ]);
    }

    /**
     * Manage the personal ignore list.
     * @return mixed
     */
    public function actionIgnorelist()
    {
        if (Yii::$app->request->isPost) {
            IgnoreListEntry::deleteAll(['user_id' => Yii::$app->user->id]);

            if (isset(Yii::$app->request->post()['ignored_users']))
                foreach (Yii::$app->request->post()['ignored_users'] as $ignored_user) {
                    $model = Yii::createObject([
                        'class' => IgnoreListEntry::className(),
                        'user_id' => Yii::$app->user->id,
                        'blocks_user_id' => $ignored_user,
                        'created_at' => date('Y-m-d G:i:s'),
                    ]);
                    $model->save();
                }
        }

        $user = new Yii::$app->controller->module->userModelClass;
        $users = $user::find()->where(['!=', 'id', Yii::$app->user->id])->all();

        $ignored_users = [];

        foreach (IgnoreListEntry::find()->select('blocks_user_id')->where(['user_id' => Yii::$app->user->id])->asArray()->all() as $ignore)
            $ignored_users[] = $ignore['blocks_user_id'];

        return $this->render('ignorelist', ['users' => $users, 'ignored_users' => $ignored_users]);
    }

    /**
     * Lists all Message models where i am the author.
     * @return mixed
     */
    public function actionSent()
    {
        $searchModel = new MessageSearch();
        $searchModel->from = Yii::$app->user->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('sent', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Mark all messages as read
     * @param integer $id
     * @return mixed
     */
    public function actionMarkAllAsRead()
    {
        foreach (Message::find()->where([
            'to' => Yii::$app->user->id,
            'status' => Message::STATUS_UNREAD])->all() as $message)
            $message->updateAttributes(['status' => Message::STATUS_READ]);

        return $this->goBack();
    }

    /**
     * Displays a single Message model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($hash)
    {
        $message = $this->findModel($hash);

        if ($message->status == Message::STATUS_UNREAD)
            $message->updateAttributes(['status' => Message::STATUS_READ]);

        return $this->render('view', [
            'message' => $message
        ]);
    }

    /**
     * Finds the Message model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($hash)
    {
        $message = Message::find()->where(['hash' => $hash])->one();

        if (!$message)
            throw new NotFoundHttpException(Yii::t('message', 'The requested message does not exist.'));

        if (Yii::$app->user->id != $message->to && Yii::$app->user->id != $message->from)
            throw new ForbiddenHttpException(Yii::t('message', 'You are not allowed to access this message.'));

        return $message;
    }

    /**
     * Compose a new Message.
     * When it is an answers to a message ($answers is set) it will set the status of the original message to
     * 'Answered'.
     * If creation is successful, the browser will be redirected to the 'inbox' page.
     * @return mixed
     */
    public function actionCompose($to = null, $answers = null)
    {
        $model = new Message();

        if (Yii::$app->request->isPost) {
            foreach (Yii::$app->request->post()['Message']['to'] as $recipient_id) {
                $model = new Message();
                $model->load(Yii::$app->request->post());
                $model->to = $recipient_id;
                $model->save();
                if ($answers) {
                    $origin = Message::find()->where(['hash' => $answers])->one();
                    if ($origin && $origin->to == Yii::$app->user->id && $origin->status == Message::STATUS_READ)
                        $origin->updateAttributes(['status' => Message::STATUS_ANSWERED]);
                }
            }
            return $this->redirect(['inbox']);
        } else {
            if ($to)
                $model->to = [$to];

            return $this->render('compose', [
                'model' => $model,
                'answers' => $answers,
                'possible_recipients' => ArrayHelper::map(Message::getPossibleRecipients(Yii::$app->user->id), 'id', 'username'),
            ]);
        }
    }

    /**
     * Deletes an existing Message model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public
    function actionDelete($hash)
    {
        $model = $this->findModel($hash);

        if ($model->to != Yii::$app->user->id)
            throw new yii\web\ForbiddenHttpException;

        $model->delete();

        return $this->redirect(['inbox']);
    }
}
