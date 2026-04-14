<div class="box bb">
	<h3>Configurações de pagamento vinti4</h3>
</div>

<div class="box">
	<form method="POST">
		<input type="hidden" name="updated" value="true" />
		<div>
			<?php
				settings_fields( 'vinti4_fields' );
	            do_settings_sections( 'vinti4_fields' );
			?>
		</div>
		<?php wp_nonce_field( 'vinti4_update', 'vinti4_form' ); ?>
		<!-- <div>
			<label>POS ID</label><br>
			<input type="text" name="pos_id">
		</div>
		<div>
			<label>POS AUTH CODE</label><br>
			<input type="text" name="pos_id" value="">
		</div> -->
		<div class="text-right">
			<br>
			<button>Atualizar</button>
		</div>
	</form>

</div>

<style>
	
	.box{
		background: #FFF;
		padding: 12px 18px;
		/*width: 600px;
		max-width: 80%;*/
		margin: 5px 15px 2px;
	}

	.bb{
		border-bottom: 1px solid #e74c3c;
		margin-bottom: 12px;
	}

	input{
		padding: 4px 8px;
		border-radius: 0;
		width: 100%;
	}

	button{
		background: #e74c3c;
		color: #FFF;
		padding: 8px 12px;
		border: none;
		width: 120px;
	}

	.text-right{
		text-align: right;
	}

</style>