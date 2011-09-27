<div class="wrap">

	<?php $form = new PW_Form( $model ); ?>
			
	<?php $form->begin_form(); ?>
	
		<?php $form->begin_section('Per Post Scripts & Styles'); ?>
		<ul>
			<li><?php $form->checkbox_list( 'post_types' ); ?></li>
			<li><?php $form->radio_button_list( 'on' ); ?></li>
			
		</ul>
		<?php $form->end_section(); ?>


	<?php $form->end_form(); ?>

</div>

