<?php

namespace thyseus\message\controllers;

use Yii;
use thyseus\message\models\Message;
use thyseus\message\models\MessageSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
     * Compose a new Message.
     * When it is an answers to a message ($answers is set) it will set the status of the original message to
     * 'Answered'.
     * If creation is successful, the browser will be redirected to the 'inbox' page.
     * @return mixed
     */
    public function actionCompose($to = null, $answers = null)
    {
        $model = new Message();

        if ($to)
            $model->to = $to;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($answers) {
                $origin = Message::find()->where(['hash' => $answers])->one();
                if ($origin && $origin->to == Yii::$app->user->id && $origin->status == Message::STATUS_READ)
                    $origin->updateAttributes(['status' => Message::STATUS_ANSWERED]);
            }
            return $this->redirect(['inbox']);
        } else {
            return $this->render('compose', [
                'model' => $model,
                'answers' => $answers,
            ]);
        }
    }

    /**
     * Deletes an existing Message model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($hash)
    {
        $this->findModel($hash)->delete();

        return $this->redirect(['inbox']);
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

        return $message;
    }
}
