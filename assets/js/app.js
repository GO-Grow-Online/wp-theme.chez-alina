jQuery(function($) {
  open_stuff();
  fixed_header();
  smooth_scroll();
  onScroll_animation();
  
  init_menu();

  function init_menu() {

    // Update menu if local Storage exists
    let order = get_order();
    update_frontend(order);


    // Enable "add_to_menu" functionnality
    $('.js--addProduct').on('click', function() {
      var id = $(this).attr('data-product');
      add_to_order(id);
    });

    // Load order. Opening it is done with open_stuff()
    $('.js--loadOrder').on('click', function() {
      load_order();
      remove_btns = $('.js--removeProduct');

      // Check if manu is open
      if ($('body').attr('data-menu-page') != "menu") {
        $('body').attr('data-menu-page', 'menu');
      }

      $('body').addClass('orderList--open');
    });

    // Delete order, require to hold button for 5 sec
    $('.js--deleteOrder').on('mousedown touchstart', function() {

      var order = [];
      $('body').addClass('anim-deletion');
      pressTimer = setTimeout(function() {
        $('body').removeClass('anim-deletion');
        $('body').removeClass('orderList--open');
        $('.product.show-units').removeClass('show-units');

        localStorage.setItem("order", JSON.stringify(order));
  
        update_frontend(order);
      }, 4000);

    }).on('mouseup mouseleave touchend', function() {
      clearTimeout(pressTimer);
      $('body').removeClass('anim-deletion');
    });

    $('.js--order').on('click', function() {
      $('body').attr('data-menu-page', 'order-method');
      $('body').removeClass('orderList--open')
    })

    $('section[data-menu-page="order-method"] .btn').on('click', function() {
      $('body').attr('data-menu-page', 'order-end');
    })

    $('js--setMethod').on('click', function() {
      let method = $(this).attr('data-method');

      console.log(method);

      $('.js--endOrder').on('click', function() {
        console.log($('a[data-method="' + method + '"]'));
        $('a[data-method="' + method + '"]').trigger('click');
      });
    });

  }

  function load_order() {

    var order = get_order();

    // Vider la liste de commande avant de l'actualiser
    $('.orderList-content').empty();

    // Grouper les produits par catégorie
    let categories = {};

    order.forEach(product => {
        let cat_id = product.id.split("-")[0];  // Supposons que la catégorie est la première partie de l'ID, avant le premier tiret
        if (!categories[cat_id]) {
            categories[cat_id] = [];
        }
        categories[cat_id].push(product);
    });


    var html = "";
    var total_price = 0;

    // Afficher chaque catégorie et ses produits
    Object.keys(categories).forEach(cat_id => {
        let categoryName = $('#' + cat_id + ' > .menu-cat-title').text();  // Récupérer le nom de la catégorie
        html += "<div class='orderList-cat'><p class='uppercase'>" + categoryName + "</p> <ul class='orderList-list'>";
        

        // Add products
        categories[cat_id].forEach(product => {
            let newItem = "<li class='orderList-cat-product product show-units' data-product='" + product.id + "'>" +
                            "<p class='product-name'>" + product.name + " (" + product.price + "€)" +
                            "<span class='units'>" + product.number + "</span></p>" +
                            "<p class='price'>" + (product.price * product.number) + "€</p>" +
                            "<button class='js--removeProduct' data-product='" + product.id + "' aria-label='Retirer une unité de la commande'><span class='icon'><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' viewBox='0 0 448 512'><path d='M432 280l-24 0L40 280l-24 0 0-48 24 0 368 0 24 0 0 48z'/></svg></span></button>" +
                          "</li>";

            // Compose total price
            let price = !product.price ? 0 : product.price * product.number;
            total_price = total_price + price;
            html += newItem;
        });
        
        html += "</ul></div>";

      });

      $('.orderList-content').html(html);

      var html = "<p class='uppercase'>TOTAL</p>" +
                "<p class='price'>" + total_price + "€</p>";


                
      $('.orderList-total').html(html);
      $('.js--removeProduct').on('click', function() {
        var id = $(this).attr('data-product');
        remove_from_order(id);
      });
  }

  function add_to_order(id) {

    var name = $('#' + id + "-name").text();
    var price = $('#' + id + "-price").attr('data-value');
    var number;
    
    // Get existing order
    var order = get_order();
    var existing_product = order.find(product => product.id === id);

    // If product is in order, increment number | if not, create and push
    if (existing_product) {
      existing_product.number += 1;
      number = existing_product.number;
    } else {
      var product = {
          id: id,
          name: name,
          price: price,
          number: 1
      };

      order.push(product);
      number = 1;
    }

    localStorage.setItem("order", JSON.stringify(order));

    update_frontend(order);
  }

  function remove_from_order(id) {

    // Get existing order
    var order = get_order();
    var existing_product = order.find(product => product.id === id);

    // If product is in order, increment number | if not, create and push
    if (existing_product) {
      if (existing_product.number > 1) { 
        existing_product.number -= 1;
      } else {
        // Remove the product if its number is 1
        order = order.filter(product => product.id !== id);
        $('#' + id).removeClass('show-units');
      }
    }

    localStorage.setItem("order", JSON.stringify(order));

    update_frontend(order);
    load_order();
  }


  function update_frontend(order) {

    // Enable "order" button
    if (order.length > 0) {
      $('.js--order').prop('disabled', false);
      $('body').removeClass('no-order');
    }else{
      $('.js--order').prop('disabled', true);
      $('body').addClass('no-order');
    }

    order.forEach(product => {
      var existing_product = order.find(product => product.id === product.id);
      update_product_frontend(product.id, product.number, existing_product);
    });

    update_preview(order);
  }


  function update_product_frontend(id, number, existing_product) {
    // Update product list item
    $('#' + id + '-units').text(number);
    $('#' + id).addClass('show-units');
  }

  function update_preview(order) {

    // Vider la liste de prévisualisation
    $('.nav_main-order-list').empty();

    // Calculer le total des unités par catégorie
    let categories = {};

    order.forEach(product => {
        let cat_id = product.id.split("-")[0];
        if (!categories[cat_id]) {
            categories[cat_id] = { total: 0, icon: $('#' + cat_id + ' > .menu-cat-title > .icon').html() };
        }
        categories[cat_id].total += product.number;
    });

    // Ajouter chaque catégorie dans la prévisualisation
    Object.keys(categories).forEach(cat_id => {
        let category = categories[cat_id];
        let show_units_class = category.total > 1 ? "show-units" : "";

        let newItem = "<li class='nav_main-order-list-item " + show_units_class + "' data-cat='" + cat_id + "'>" +
                        "<span class='units'>" + category.total + "</span>" +
                        "<span class='icon'>" + category.icon + "</span>" +
                      "</li>";
        $('.nav_main-order-list').append(newItem);
    });
  }





  function get_order() {
    return JSON.parse(localStorage.getItem("order")) || [];
  }

  function smooth_scroll() {
    
    var scroll_duration = 500; // Trasition duration
    var space_from_top = ($(window).height() / 4);
    
    // Smooth scroll on anchor link click
    $('a[href^="#"]').each(function() {
      $(this).on('click', function (e) {
        e.preventDefault();
        
        var scroll_target = $(this).attr('href');
        var scroll_target = $(scroll_target);

        if (scroll_target.length) {
          $('html, body').animate({
            scrollTop: scroll_target.offset().top - space_from_top
          }, scroll_duration);
        }
      });
    });

    // Smooth scroll on page load if id in url
    if (window.location.hash) {
      var scroll_target = $(window.location.hash);
      if (scroll_target.length) {
          setTimeout(() => {              
            $('html, body').animate({
                scrollTop: scroll_target.offset().top - space_from_top
            }, scroll_duration);
          }, 100);
      }
    }
  }

  function onScroll_animation() {
    var show_at_breakpoint = '1200px';
    var viewport_portion = window.matchMedia('(max-width: ' + show_at_breakpoint + ')').matches ? 100 : 300;
  
    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          var visiblePixels = entry.intersectionRect.height;
          if (visiblePixels >= viewport_portion) {
            $(entry.target).removeClass('hidden-animate');
          }
        }
      });
    }, {
      threshold: Array.from({ length: 101 }, (_, i) => i * 0.01)
    });
  
    var hidden_elements = $('.hidden-animate');
    hidden_elements.each(function() {
      observer.observe(this);
    });
  }

  function fixed_header() {
    $(window).scroll(function (event) {
      var scroll = $(window).scrollTop();
      if (scroll > $('header').height()) {
        $('body').addClass('scrolled');
      }else{
        $('body').removeClass('scrolled');
      }
    });
  }
  
  function open_stuff() {
    $('.js--open').on('click', function() {
      $('body').addClass($(this).attr('data-open') + "--open");
    })
  
    $('.js--close').on('click', function() {
      $('body').removeClass($(this).attr('data-close') + "--open");
    })
  
    $('.js--toggle').on('click', function() {
      $('body').toggleClass($(this).attr('data-toggle') + "--open");
    })
  }

});