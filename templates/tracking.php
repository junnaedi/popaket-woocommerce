<?php
get_header();

while ( have_posts() ) :
    the_post();
    ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php if ( $tracking_data && $tracking_data->status_code == 200 ) : ?>
                    <div class="card p-5" style="width: 500px; margin: auto;">
                        <h1 class="fs-4 fw-bold">Tracking</h1>
                        <h2 class="text-secondary fs-6 fw-bold">No. AWB / Resi <?php echo $awb_number ?></h2>
                        <div class="tracking-details mt-3 fw-bold text-secondary">
                            <div class="row fs-7 mb-2">
                                <div class="col-md-6">Weight</div>
                                <div class="col-md-6 text-start"><?php echo esc_html( $tracking_data->data->weight ) ?> kg</div>
                            </div>
                            <div class="row fs-7 mb-2">
                                <div class="col-md-6">Lokasi Asal</div>
                                <div class="col-md-6 text-start"><?php echo esc_html( $tracking_data->data->origin ) ?></div>
                            </div>
                            <div class="row fs-7 mb-2">
                                <div class="col-md-6">Lokasi Tujuan</div>
                                <div class="col-md-6 text-start"><?php echo esc_html( $tracking_data->data->destination ) ?></div>
                            </div>
                            <div class="row fs-7 mb-2">
                                <div class="col-md-6">Tracking Code</div>
                                <div class="col-md-6 text-start"><?php echo esc_html( $tracking_data->data->tracking_code ) ?></div>
                            </div>
                        </div>
                        <?php if ( ! empty( $tracking_data->tracking_history ) ) : ?>
                        <h2 class="text-secondary fs-6 fw-bold mt-4">Tracking History</h2>
                        <table>
                            <tbody>
                                <?php foreach ( $tracking_data->tracking_history as $history ) : ?>
                                    <tr>
                                        <td>Tanggal</td>
                                        <td><?php echo esc_html( $history->date ) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Status</td>
                                        <td><?php echo esc_html( $history->status ) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Description</td>
                                        <td><?php echo esc_html( $history->description ) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <div style="width: 500px; margin: auto;">
                        <h1 class="fs-4 fw-bold">Tracking</h1>
                        <h2 class="text-secondary fs-6 fw-bold"><?php echo esc_html( $tracking_data->message ) ?></h2>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php
endwhile;

get_footer();