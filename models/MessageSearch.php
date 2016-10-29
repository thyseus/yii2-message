<?php

namespace thyseus\message\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use thyseus\message\models\Message;

/**
 * MessageSearch represents the model behind the search form about `app\models\Message`.
 */
class MessageSearch extends Message
{
    public $inbox = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'from', 'to', 'status'], 'integer'],
            [['hash', 'status', 'title', 'message', 'created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function beforeValidate()
    {
        return true;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Message::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['created_at'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'from' => $this->from,
            'to' => $this->to,
            'created_at' => $this->created_at,
            'status' => $this->status,
        ]);

        if($this->inbox)
          $query->andFilterWhere(['>=', 'status', 0]);

        $query->andFilterWhere(['like', 'hash', $this->hash])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}
