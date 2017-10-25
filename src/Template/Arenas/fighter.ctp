<?php $this->assign('title', 'Fighters');?>

<h1><?= $this->fetch('title') ?></h1>


<ul class="list-unstyled col-md-6 col-md-offset-3">
	<?php foreach ($list as $fighter): ?>
		<li>
			<article class="panel <?=($fighter['current_health']==0)?'panel-danger':'panel-success'; ?>">
				<h5 class="panel-heading"><?= $fighter['name'] ?></h5>
				<div class="panel-body">
					<?php if (file_exists(WWW_ROOT.'/img/avatars/'.$fighter->id.'.jpg')): ?>
					<?= $this->Html->image('avatars/'.$fighter->id.'.jpg', ['width' => '50px', 'height' => '50px', 'alt' => 'Avatar', 'class' => 'center-block']); ?>
					<?php else: ?>
					<?= $this->Html->image('avatars/default.jpg', ['width' => '50px', 'height' => '50px', 'alt' => 'Avatar', 'class' => 'center-block']); ?>
					<?php endif; ?>
					<dl class="dl-horizontal">
						<dt>Health</dt>
						<dd><?= $fighter['current_health'].'/'.$fighter['skill_health'] ?></dd>
						<dt>XP</dt>
						<dd><?= $fighter['xp'] ?></dd>
						<dt>Coord</dt>
						<dd><?= $fighter['coordinate_x'].','.$fighter['coordinate_y']  ?></dd>
					</dl>
				</div>
			</article>
		</li>
	<?php endforeach; ?>
</ul>

<?php if (!$hasAliveFighter): ?>
	<p class="col-md-6 col-md-offset-3">All your fighters are dead...
		<?= $this->Html->link('Create a new fighter', ['action' => './addFighter']); ?>
		?</p>
	<?php endif; ?>
