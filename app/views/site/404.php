<section class="ph ph-404">
  <img class="ph-bg" src="<?= e(asset('assets/img/baku-night.jpg')) ?>" alt="">
  <div class="hs-grid" aria-hidden="true"></div>
  <div class="wrap ph-in">
    <p class="chip"><span class="dot"></span>404</p>
    <h1><?= e(tx('e404.title')) ?></h1>
    <p class="lead"><?= e(tx('e404.text')) ?></p>
    <div class="hs-cta"><a class="btn btn-lime" href="<?= e(route('home')) ?>"><?= e(tx('e404.back')) ?><?= icon('arrow') ?></a>
      <a class="btn btn-glass" href="<?= e(route('services')) ?>"><?= e(tx('nav.services')) ?></a></div>
  </div>
</section>
