<?php

namespace thyseus\message\controllers;

use app\models\User;
use thyseus\message\events\MessageSentEvent;
use thyseus\message\models\AllowedContacts;
use thyseus\message\models\IgnoreListEntry;
use thyseus\message\models\Message;
use thyseus\message\models\MessageSearch;
use Yii;
use yii\db\IntegrityException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * MessageController handles all user actions related to the yii2-message module
 */
class MessageController extends Controller
{
    const EVENT_BEFORE_SEND = 'event_before_send';
    const EVENT_AFTER_SEND = 'event_after_send';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'inbox', 'drafts', 'signature', 'out-of-office', 'ignorelist',
                            'sent', 'compose', 'view', 'delete', 'mark-all-as-read',
                            'check-for-new-messages', 'manage-draft'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /** Simply print the count of unread messages for the currently logged in user.
     * If it is only one unread message, display an link to it.
     * Useful if you want to implement a automatic notification for new users using
     * the longpoll method (e.g. query every 10 seconds).
     * To ensure the user is not being bugged too often, we only display the
     * "new messages" message once every <newMessagesEverySeconds> per session.
     * This defaults to 3600 (once every hour). */
    public function actionCheckForNewMessages()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;

        $session = Yii::$app->session;

        $key = 'last_check_for_new_messages';
        $last = 'last_response_when_checking_for_new_messages';

        if ($session->has($key)) {
            $last_check = $session->get($key);
        } else {
            $last_check = time();
        }

        $conditions = ['to' => Yii::$app->user->id, 'status' => 0];

        $count = Message::find()->where($conditions)->count();

        $time_bygone = time() > $last_check + Yii::$app->getModule('message')->newMessagesEverySeconds;

        if ($count == 1) {
            $message = Message::find()->where($conditions)->one();

            if ($message) {
                if ($message->title != $session->get($last) || $time_bygone) {
                    return Html::a($message->title, ['//message/message/view', 'hash' => $message->hash]);
                    Yii::$app->session->set($last, $message->title);
                } else
                    return 0;
            }
        } else {
            if ($count != $session->get($last) || $time_bygone) {
                return $count;
                Yii::$app->session->set($last, $count);

            } else {
                return 0;
            }
        }

        Yii::$app->session->set($key, time());
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

        Yii::$app->user->setReturnUrl(['//message/message/inbox']);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('inbox', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'users' => Message::userFilter(Yii::$app->user->id),
        ]);
    }

    /**
     * Lists all Message models where i am the recipient.
     * @return mixed
     */
    public function actionDrafts()
    {
        $searchModel = new MessageSearch();
        $searchModel->from = Yii::$app->user->id;
        $searchModel->draft = true;

        Yii::$app->user->setReturnUrl(['//message/message/drafts']);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('drafts', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'users' => $this->recipientsFor(Yii::$app->user->id),
        ]);
    }

    protected function recipientsFor($user_id)
    {
        return ArrayHelper::map(
            Message::find()
                ->where(['from' => Yii::$app->user->id])
                ->select('to')
                ->groupBy('to')
                ->all(), 'to', 'recipient.username');
    }

    /**
     * Manage the personal ignore list.
     * @return mixed
     */
    public function actionIgnorelist()
    {
        Yii::$app->user->setReturnUrl(['//message/message/ignorelist']);

        if (Yii::$app->request->isPost) {
            IgnoreListEntry::deleteAll(['user_id' => Yii::$app->user->id]);

            if (isset(Yii::$app->request->post()['ignored_users'])) {
                foreach (Yii::$app->request->post()['ignored_users'] as $ignored_user) {
                    $model = Yii::createObject([
                        'class' => IgnoreListEntry::class,
                        'user_id' => Yii::$app->user->id,
                        'blocks_user_id' => $ignored_user,
                        'created_at' => date('Y-m-d G:i:s'),
                    ]);

                    if ($model->save()) {
                        Yii::$app->session->setFlash(
                            'success', Yii::t('message',
                            'The list of ignored users has been saved'));
                    } else {
                        Yii::$app->session->setFlash(
                            'error', Yii::t('message',
                            'The list of ignored users could not be saved'));
                    }
                }
            }
        }

        $users = Message::getPossibleRecipients(Yii::$app->user->id);

        $ignored_users = [];

        foreach (IgnoreListEntry::find()
                     ->select('blocks_user_id')
                     ->where(['user_id' => Yii::$app->user->id])
                     ->asArray()->all() as $ignore) {
            $ignored_users[] = $ignore['blocks_user_id'];
        }

        return $this->render('ignorelist', [
            'users' => $users,
            'ignored_users' => $ignored_users,
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
        $searchModel->sent = true;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Yii::$app->user->setReturnUrl(['//message/message/sent']);

        return $this->render('sent', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'users' => $this->recipientsFor(Yii::$app->user->id),
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
            'status' => Message::STATUS_UNREAD,
        ])->all() as $message) {
            $message->updateAttributes(['status' => Message::STATUS_READ]);
        }

        Yii::$app->session->setFlash(
            'success', Yii::t('message',
            'All messages in your inbox have been marked as read'));

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Displays a single Message model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($hash)
    {
        $message = $this->findModel($hash);

        if ($message->status == Message::STATUS_UNREAD && $message->to == Yii::$app->user->id)
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
     *
     * When it is an answers to a message ($answers is set) it will set the status of the original message to 'Answered'.
     * You can set an 'context' to link this message on to an entity inside your application. This should be an
     * id or slug or other identifier.
     *
     * If $to and $add_to_recipient_list is set, the recipient will be added to the allowed contacts list. The sender
     * will also be included in the recipient´s allowed contact list. Use this to allow first contact between users
     * in an application where contacts are limited.
     *
     * If creation is successful, the browser will be redirected to the referrer, or 'inbox' page if not set.
     *
     * When this action is called by an Ajax Request, the view is prepared to return a partial view. This is useful
     * if you want to render the compose form inside a Modal.
     *
     * Since 0.4.0:
     *
     * Depending on the submit button that has been used we probably save the message as a draft
     * instead of sending it directly.
     *
     * When a signature is given by the user, we preload the signature in the message.
     *
     * @see README.md
     * @var $to integer|null The 'recipient' attribute will be prefilled with the user of this id
     * @var $answers string|null This message will be marked as an answer to the message of this hash
     * @var $context string|null This message is related to an entity accessible through this url
     * @var $add_to_recipient_list bool This users did not yet have contact, add both of them to their contact list
     * @since 0.3.0
     * @throws NotFoundHttpException When the user is not found in the database anymore.
     * @throws ForbiddenHttpException When the user is on the ignore list.
     * @return mixed
     */
    public function actionCompose($to = null, $answers = null, $context = null, $add_to_recipient_list = false)
    {
        $this->trigger(self::EVENT_BEFORE_SEND);

        if (Yii::$app->request->isAjax) {
            $this->layout = false;
        }

        if (Message::isUserIgnoredBy($to, Yii::$app->user->id)) {
            return $this->render('you_are_ignored');
        }

        if ($add_to_recipient_list && $to) {
            $this->add_to_recipient_list($to);
        }

        $model = new Message();
        $possible_recipients = Message::getPossibleRecipients(Yii::$app->user->id);

        if (!Yii::$app->user->returnUrl) {
            Yii::$app->user->setReturnUrl(Yii::$app->request->referrer);
        }

        if ($answers) {
            $origin = Message::find()->where(['hash' => $answers])->one();

            if (!$origin) {
                throw new NotFoundHttpException(
                    Yii::t('message', 'Message to be answered can not be found'));
            }
        }

        if (Yii::$app->request->isPost) {
            $recipients = Yii::$app->request->post()['Message']['to'];

            if (is_numeric($recipients)) { # Only one recipient given
                $recipients = [$recipients];
            }

            if (isset($_POST['save-as-draft'])) {
                $this->saveDraft(Yii::$app->user->id, Yii::$app->request->post()['Message']);
            } else {
                foreach ($recipients as $recipient_id) {
                    $this->sendMessage($recipient_id, Yii::$app->request->post()['Message'], $answers ? $origin : null);
                }
            }

            return Yii::$app->request->isAjax ? true : $this->goBack();
        }

        $model = $this->prepareCompose($to, $model, $answers ? $origin : null, $context);

        return $this->render('compose', [
            'model' => $model,
            'answers' => $answers,
            'origin' => isset($origin) ? $origin : null,
            'context' => $context,
            'dialog' => Yii::$app->request->isAjax,
            'allow_multiple' => true,
            'possible_recipients' => ArrayHelper::map($possible_recipients, 'id', 'username'),
        ]);
    }

    /**
     * @param $to
     * @param Message $model
     * @param null $origin
     * @return Message
     */
    protected function prepareCompose($to, Message $model, $origin = null, $context = null): Message
    {
        if (is_numeric($to)) {
            $model->to = [$to];
        }

        if ($context) {
            $model->context = $context;
        }

        if ($origin) {
            $prefix = Yii::$app->getModule('message')->answerPrefix;

            // avoid stacking of prefixes (Re: Re: Re:)
            if (substr($origin->title, 0, strlen($prefix)) !== $prefix) {
                $model->title = $prefix . $origin->title;
            } else {
                $model->title = $origin->title;
            }

            $model->context = $origin->context;
        }

        if ($signature = Message::getSignature(Yii::$app->user->id)) {
            $model->message = $signature->message;
        }

        return $model;
    }

    /**
     *
     * @param int $recipient_id the user that receives the message
     * @param array $attributes the incoming $_POST data
     * @param $origin provide an message that this message should be the answer to
     * @return bool success state of save()
     */
    protected function sendMessage(int $recipient_id, array $attributes, $origin = null): bool
    {
        $model = new Message();
        $model->attributes = $attributes;
        $model->from = Yii::$app->user->id;
        $model->to = $recipient_id;
        $model->status = Message::STATUS_UNREAD;

        if ($model->save()) {
            if ($origin && $origin->to == Yii::$app->user->id && $origin->status == Message::STATUS_READ) {
                $origin->updateAttributes(['status' => Message::STATUS_ANSWERED]);

                Yii::$app->session->setFlash('success', Yii::t('message',
                    'The message has been answered.'));
            } else {
                Yii::$app->session->setFlash('success', Yii::t('message',
                    'The message has been sent.'));
            }

            $event = new MessageSentEvent;
            $event->postData = Yii::$app->request->post();
            $event->message = $model;
            $this->trigger(self::EVENT_AFTER_SEND, $event);

            return true;
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('message',
                'The message could not be sent: ' . implode(', ', $model->getErrorSummary(true))));
            return false;
        }
    }

    /**
     * @param $from the user that owns the draft
     * @param $post the incoming $_POST data
     */
    protected function saveDraft(int $from, array $attributes): bool
    {
        $model = new Message();
        $model->attributes = $attributes;
        $model->status = Message::STATUS_DRAFT;
        $model->from = Yii::$app->user->id;

        if ($model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('message',
                'The message has been saved as draft'));
            return true;
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('message',
                'The message could not be saved as draft: ') . implode(', ', $model->getErrorSummary(true)));
            return true;
        }
    }

    /**
     * @param $to
     * @throws NotFoundHttpException
     */
    protected function add_to_recipient_list($to)
    {
        if ($recipient = User::findOne($to)) {
            try {
                $ac = new AllowedContacts();
                $ac->user_id = Yii::$app->user->id;
                $ac->is_allowed_to_write = $to;
                $ac->save();

                $ac = new AllowedContacts();
                $ac->user_id = $to;
                $ac->is_allowed_to_write = Yii::$app->user->id;
                $ac->save();
            } catch (IntegrityException $e) {
                // ignore integrity constraint violation in case users are already connected
            }
        } else throw new NotFoundHttpException();
    }

    /**
     * Handle the signature
     * @return string
     */
    public function actionSignature()
    {
        $signature = Message::getSignature(Yii::$app->user->id);

        if (!$signature) {
            $signature = new Message;
            $signature->title = 'Signature';
            $signature->status = Message::STATUS_SIGNATURE;

            Yii::$app->session->setFlash(
                'success', Yii::t('message',
                'You do not have an signature yet. You can set it here.'));
        }

        if (Yii::$app->request->isPost) {
            $signature->load(Yii::$app->request->post());
            $signature->from = Yii::$app->user->id;
            $signature->save();

            Yii::$app->session->setFlash(
                'success', Yii::t('message',
                'Your signature has been saved.'));
        }

        return $this->render('signature', ['signature' => $signature]);
    }

    public function actionManageDraft($hash = null)
    {
        if ($hash) {
            $draft = Message::find()->where(['from' => Yii::$app->user->id, 'hash' => $hash])->one();
            if (!$draft) {
                throw new NotFoundHttpException();
            }
        } else {
            $draft = new Message;
        }

        $draft->status = Message::STATUS_DRAFT;
        $draft->from = Yii::$app->user->id;
        $possible_recipients = Message::getPossibleRecipients(Yii::$app->user->id);

        if (Yii::$app->request->isPost) {
            $draft->load(Yii::$app->request->post());

            if (isset($_POST['save-draft'])) {
                $this->saveDraft(Yii::$app->user->id, $draft->attributes);

                Yii::$app->user->setReturnUrl(['//message/message/drafts']);
            } else if (isset($_POST['send-draft'])) {
                $this->sendMessage($draft->to, $draft->attributes, null);
                Yii::$app->user->setReturnUrl(['//message/message/inbox']);
            }

            return $this->goBack();
        }

        return $this->render('draft', [
            'draft' => $draft,
            'possible_recipients' => ArrayHelper::map($possible_recipients, 'id', 'username'),
        ]);
    }

    /**
     * Deletes an existing Message model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($hash)
    {
        $model = $this->findModel($hash);

        if (in_array($model->status, [
                Message::STATUS_READ,
                Message::STATUS_UNREAD,
                Message::STATUS_ANSWERED,
            ]) && $model->to != Yii::$app->user->id) {
            throw new ForbiddenHttpException;
        }

        if (in_array($model->status, [
                Message::STATUS_DRAFT,
            ]) && $model->from != Yii::$app->user->id) {
            throw new ForbiddenHttpException;
        }

        $model->delete();

        Yii::$app->session->setFlash(
            'success', Yii::t('message',
            'The message has been deleted.'));

        return $this->redirect(Yii::$app->request->referrer);
    }
}
