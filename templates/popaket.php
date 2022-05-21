<?php
$popaket_config = get_option( 'woocommerce_popaket_settings' );
?>
<div class="wrap popaket-config">
  <h1>Popaket settings</h1>
  <form method="post" action="<?php echo admin_url( 'admin-post.php' ) ?>">
    <div class="pt-card">
      <h2>Authentication</h2>
      <div class="pt-group">
        <label for="">Client Key</label>
        <input type="text" name="client_key" value="<?php echo isset( $popaket_config['client_key'] ) ? $popaket_config['client_key'] : '' ?>">
      </div>
      <div class="pt-group">
        <label for="">Client Secret</label>
        <input type="text" name="client_secret" value="<?php echo isset( $popaket_config['client_secret'] ) ? $popaket_config['client_secret'] : '' ?>">
      </div>
      <div class="pt-group">
        <label for="">Status</label>
        <select name="status">
          <option value="sandbox" <?php echo isset( $popaket_config['status'] ) && $popaket_config['status'] == 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
          <option value="production" <?php echo isset( $popaket_config['status'] ) && $popaket_config['status'] == 'production' ? 'selected' : '' ?>>Production</option>
        </select>
      </div>
    </div>
    <div class="pt-card">
      <h2>Sender Information</h2>
      <div class="pt-group">
        <label for="">Name</label>
        <input type="text" name="sender_name" value="<?php echo isset( $popaket_config['sender_name'] ) ? $popaket_config['sender_name'] : '' ?>">
      </div>
      <div class="pt-group">
        <label for="">Phone</label>
        <input type="tel" name="sender_phone" value="<?php echo isset( $popaket_config['sender_phone'] ) ? $popaket_config['sender_phone'] : '' ?>">
      </div>
      <div class="pt-group">
        <label for="">Email</label>
        <input type="email" name="sender_email" value="<?php echo isset( $popaket_config['sender_email'] ) ? $popaket_config['sender_email'] : '' ?>">
      </div>
    </div>
    <div class="pt-card">
      <h2>Pickup Address</h2>
      <div class="pt-group">
        <label for="">Address</label>
        <input type="text" name="origin_address" value="<?php echo isset( $popaket_config['origin_address'] ) ? $popaket_config['origin_address'] : '' ?>">
      </div>
      <div class="pt-group">
        <label for="">Address note</label>
        <input type="tel" name="origin_addess_note" value="<?php echo isset( $popaket_config['origin_addess_note'] ) ? $popaket_config['origin_addess_note'] : '' ?>">
      </div>
      <div class="pt-group">
        <label for="">Postal Code</label>
        <input type="text" name="origin_postal_code" value="<?php echo isset( $popaket_config['origin_postal_code'] ) ? $popaket_config['origin_postal_code'] : '' ?>">
      </div>
    </div>
    <div class="pt-card">
      <h2>Additional Configuration</h2>
      <div class="pt-group">
        <input type="checkbox" name="use_smart_api" id="use_smart_api" value="yes" <?php echo isset( $popaket_config['use_smart_api'] ) && $popaket_config['use_smart_api'] == 'yes' ? 'checked' : '' ?>>
        <label for="use_smart_api">Enable Smart API Rate Services</label>
      </div>
      <div class="pt-group">
        <label for="">Default Weight</label>
        <input type="number" name="default_weight" value="<?php echo isset( $popaket_config['default_weight'] ) ? $popaket_config['default_weight'] : '' ?>">
      </div>
      <div class="pt-group">
        <input type="checkbox" name="use_insurance" id="use_insurance" value="yes" <?php echo isset( $popaket_config['use_insurance'] ) && $popaket_config['use_insurance'] == 'yes' ? 'checked' : '' ?>>
        <label for="use_insurance">Tambahkan pilihan asuransi pengiriman di Halaman Checkout</label>
      </div>
      <div class="pt-group">
        <label for="">Enabled Courier</label>
        <select name="enabled_courier[]" multiple="true">
          <option value=""></option>
          <option value="JNE" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'JNE', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>JNE</option>
          <option value="SiCepat" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'SiCepat', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>SiCepat</option>
          <option value="SAP Logistic" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'SAP Logistic', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>SAP Logistic</option>
          <option value="Wahana" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Wahana', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Wahana</option>
          <option value="Tiki" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Tiki', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Tiki</option>
          <option value="RPX" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'RPX', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>RPX</option>
          <option value="Ninja Express" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Ninja Express', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Ninja Express</option>
          <option value="Indah Logistik" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Indah Logistik', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Indah Logistik</option>
          <option value="REX" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'REX', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>REX</option>
          <option value="Pos Indonesia" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Pos Indonesia', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Pos Indonesia</option>
          <option value="Lion Parcel" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Lion Parcel', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Lion Parcel</option>
          <option value="Sentral Cargo" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Sentral Cargo', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Sentral Cargo</option>
          <option value="Lalamove" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Lalamove', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Lalamove</option>
          <option value="Grab" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'GRAB', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Grab</option>
          <option value="POP Express" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'POP Express', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>POP Express</option>
          <option value="AnterAja" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'AnterAja', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>AnterAja</option>
          <option value="J&T Express" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'J&T Express', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>J&T Express</option>
          <option value="Paxel" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Paxel', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Paxel</option>
          <option value="Gojek" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Gojek', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Gojek (GoSend)</option>
          <option value="Borzo" <?php echo isset( $popaket_config['enabled_courier'] ) && in_array( 'Borzo', $popaket_config['enabled_courier'] ) ? 'selected' : '' ?>>Borzo (Mr Speedy)</option>
        </select>
        <p style="font-size: 10px; margin: 0; margin-top: 7px; font-style: italic;">This will not be used when you are using Smart API Rate</p>
      </div>
      <div class="pt-group">
        <label for="">Google Maps API Key</label>
        <input type="text" name="maps_api_key" value="<?php echo isset( $popaket_config['maps_api_key'] ) ? $popaket_config['maps_api_key'] : '' ?>">
      </div>
    </div>
    <input type="hidden" name="action" value="popaket_save_settings">
    <?php
    wp_nonce_field( 'popaket_save_settings' );
    submit_button();
    ?>
  </form>
</div>