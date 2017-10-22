<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Fighters Model
 *
 * @property |\Cake\ORM\Association\BelongsTo $Players
 * @property |\Cake\ORM\Association\BelongsTo $Guilds
 * @property |\Cake\ORM\Association\HasMany $Messages
 * @property |\Cake\ORM\Association\HasMany $Tools
 *
 * @method \App\Model\Entity\Fighter get($primaryKey, $options = [])
 * @method \App\Model\Entity\Fighter newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Fighter[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Fighter|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Fighter patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Fighter[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Fighter findOrCreate($search, callable $callback = null, $options = [])
 */
class FightersTable extends Table
{

    public function getFighters($id)
    {
            return $this->find()->where(['player_id' => $id])->toList();
    }

    public function isFighterHere($x, $y)
    {
            return $this->find()
                    ->where(['current_health >' => 0, 'coordinate_x' => $x, 'coordinate_y' => $y])
                    ->count() > 0;
    }
    public function getSightArray($pid, $width, $heigth)
    {
            $array = array();

            for($i=0; $i<$heigth; $i++)
            {
                    $cols = array();
                    for($j=0; $j<$width; $j++)
                    {
                            array_push($cols, '.');
                    }
                    array_push($array, $cols);
            }


            $pos = $this->getPosition($pid);
            $array[$pos['coordinate_y']][$pos['coordinate_x']] = 'M';

            $res = $this->find()
                    ->select(['coordinate_x', 'coordinate_y'])
                    ->where(['player_id !=' => $pid, 'current_health >' => 0]);

            foreach($res as $row)
            {
                    $array[$row['coordinate_y']][$row['coordinate_x']]= 'E';
            }

            return $array;
    }

    //check if the player has remaining alive fighter
    public function hasAliveFighter($pid)
    {
            return $this->find()
                    ->where(['player_id' => $pid, 'current_health >' => 0])
                    ->count() > 0;
    }



    public function getPosition($pid)
    {
        $pos = $this->find()
			->select(['coordinate_x', 'coordinate_y'])
			->where(['player_id' => $pid, 'current_health >' => 0])
			->first();
        return($pos->toArray());
    }

    public function move($pid,$x, $y,$sightArray,$height,$width)
    {
        $fighter = $this->find()->where(['player_id' => $pid, 'current_health >' => 0])->first();
        $fighter_data = $fighter->toArray();
        $tempo_coord_x=$x + $fighter_data['coordinate_x'];
        $tempo_coord_y=$y + $fighter_data['coordinate_y'];
        if($tempo_coord_x >=0 && $tempo_coord_x < $width && $tempo_coord_y >=0 && $tempo_coord_y < $height )
        {
            if($sightArray[$tempo_coord_y][$tempo_coord_x]=='.')
            {
                $fighter->coordinate_x = $tempo_coord_x;
                $fighter->coordinate_y = $tempo_coord_y;
                $this->save($fighter); 
            }
            if($sightArray[$tempo_coord_y][$tempo_coord_x]=='W' || $sightArray[$tempo_coord_y][$tempo_coord_x]=='T' )
            {
                //$fighter->current_health=0;
                // on le kill 
               // $this->save($fighter); 
            } 
        }    
    }

	
	
    public function getBestFighter()
    {
      $res = $this->find()
	  ->select(['name', 'xp', 'level'])
	  ->order(['level' => 'DESC'])
	  ->first();
	  //->groupBy('level');
      //pr($res);
      $best = array();
      array_push($best, $res['name']);
      array_push($best, $res['xp']);
      array_push($best, $res['level']);
      return $best;
    }


    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('fighters');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Players', [
            'foreignKey' => 'player_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Guilds', [
            'foreignKey' => 'guild_id'
        ]);
        $this->hasMany('Messages', [
            'foreignKey' => 'fighter_id'
        ]);
        $this->hasMany('Tools', [
            'foreignKey' => 'fighter_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('name')
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->integer('coordinate_x')
            ->requirePresence('coordinate_x', 'create')
            ->notEmpty('coordinate_x');

        $validator
            ->integer('coordinate_y')
            ->requirePresence('coordinate_y', 'create')
            ->notEmpty('coordinate_y');

        $validator
            ->integer('level')
            ->requirePresence('level', 'create')
            ->notEmpty('level');

        $validator
            ->integer('xp')
            ->requirePresence('xp', 'create')
            ->notEmpty('xp');

        $validator
            ->integer('skill_sight')
            ->requirePresence('skill_sight', 'create')
            ->notEmpty('skill_sight');

        $validator
            ->integer('skill_strength')
            ->requirePresence('skill_strength', 'create')
            ->notEmpty('skill_strength');

        $validator
            ->integer('skill_health')
            ->requirePresence('skill_health', 'create')
            ->notEmpty('skill_health');

        $validator
            ->integer('current_health')
            ->requirePresence('current_health', 'create')
            ->notEmpty('current_health');

        $validator
            ->dateTime('next_action_time')
            ->allowEmpty('next_action_time');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['player_id'], 'Players'));
        $rules->add($rules->existsIn(['guild_id'], 'Guilds'));

        return $rules;
    }
}
