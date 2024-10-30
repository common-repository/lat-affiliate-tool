'use strict';

function encrypt_post_content(data = null) {
  const data_to_str = JSON.stringify(data);
  const str_to_base64 = btoa(encodeURIComponent(data_to_str));
  return str_to_base64;
}

jQuery(document).ready(($) => {
  if (typeof latat_ajax_object == 'undefined') {
    return;
  }

  if (latat_ajax_object.tab == 'list') {
    $('#btn-open-create-form').on('click', () => {
      $('.modal').addClass('is-active');
    });
    $('.close-modal').on('click', () => {
      $('.modal').removeClass('is-active');
    });
    $('#btn-create').on('click', () => {
      $('#btn-create').prop('disabled', true).addClass('btn-loading');
      const table_name = $('#new-table-name').val().trim();
      if (!table_name) {
        alert('Please enter table name!');
        return;
      } else {
        const new_table_data = {
          action: 'save_table',
          post_title: table_name,
          post_content: encrypt_post_content({
            display_style: 3,
            products: [],
            custom_columns: [],
          }),
        };

        $.post(latat_ajax_object.ajax_url, new_table_data, function (response) {
          response = JSON.parse(response);
          if (response.success) {
            window.location.href = latat_ajax_object.edit_table_url + response.table_id;
          } else {
            $('#btn-create').prop('disabled', false).removeClass('btn-loading');
            alert('Error, please try again later!');
            return;
          }
        });
      }
    });
  } else if (latat_ajax_object.tab == 'edit') {
    const { table_data } = latat_ajax_object;

    // ================================
    // load products
    doLoadProducts(table_data.products);

    function doLoadProducts(products = []) {
      for (let i in products) {
        if (!products[i]) {
          continue;
        }

        const item = products[i];
        let item_row = $(`<tr id="product-list-item-${item.ASIN}"></tr>`);

        const button_up = $('<button class="button is-small button-up"><span class="icon is-small"><i class="fas fa-arrow-up"></i></span></button>').on('click', (e) => {
          let btn = $(e.target).closest('button');
          item_row.prev().before(item_row);
          if (item_row.is(':first-child')) {
            btn.prop('disabled', true);
          }
          if (item_row.next().is(':last-child')) {
            item_row.next().find('.button-down').prop('disabled', true);
          }
          item_row.find('.button-down').removeProp('disabled');
          item_row.next().find('.button-up').removeProp('disabled');

          const i_to = +i - 1;
          [products[i_to], products[i]] = [products[i], products[i_to]];
          i = i_to;
        });

        const button_down = $('<button class="button is-small button-down"><span class="icon is-small"><i class="fas fa-arrow-down"></i></span></button>').on('click', (e) => {
          let btn = $(e.target).closest('button');
          item_row.next().after(item_row);
          if (item_row.is(':last-child')) {
            btn.prop('disabled', true);
          }
          if (item_row.prev().is(':first-child')) {
            item_row.prev().find('.button-up').prop('disabled', true);
          }
          item_row.find('.button-up').removeProp('disabled');
          item_row.prev().find('.button-down').removeProp('disabled');
          const i_to = +i + 1;
          [products[i_to], products[i]] = [products[i], products[i_to]];
          i = i_to;
        });

        // Image.
        item_row.append($('<td></td>').append(button_up).append(button_down));

        // Image.
        item_row.append(
          $('<td></td>')
            .addClass('column-thumbnail')
            .append($('<img/>').attr({ src: item.image, width: 100, height: 100 }))
        );

        // Title & Detail page URL
        const product_title_input = $(`<textarea rows="5" style="width: 100%; font-weight: 500">${item.title}</textarea>`)
          .on('change', (e) => {
            item.title = $(e.target)
              .val()
              .replace(/(\r\n|\n|\r)/gm, '');
          })
          .on('keydown', function (e) {
            if (e.which == 13) {
              return false;
            }
          });

        item_row.append(
          $('<td></td>')
            .addClass('column-title')
            .append(product_title_input)
            .append(
              $('<div></div>')
                .addClass('row-actions')
                .append('<span><a href="' + item.detail_page_url + '" target="_blank">View detail |</a></span>')
                .append(
                  $('<span class="trash"></span>').append(
                    $('<a href="#">Delete</a>').on('click', () => {
                      table_data.products = table_data.products.filter((p) => p.ASIN != item.ASIN);
                      $(`#product-list-item-${item.ASIN}`).remove();
                    })
                  )
                )
            )
        );

        // ====================================
        // Custom badge
        const custom_badge_enabled = item.custom_badge.enabled == 'true' && item.custom_badge.enabled == true;
        const custom_badge_checkbox = $(`<input type="checkbox"/>`)
          .prop('checked', custom_badge_enabled)
          .on('change', (e) => {
            item.custom_badge.enabled = $(e.target).is(':checked');
            $(`.custom-badge-input-${item.ASIN}`).attr('readonly', !$(e.target).is(':checked'));
            $(`.custom-badge-background-input-${item.ASIN}`).attr('readonly', !$(e.target).is(':checked'));
          });

        // custom badge text label
        const custom_badge_checkbox_label = $(`<label></label>`).css({ display: 'block', marginBottom: '5px' }).append(custom_badge_checkbox).append('Enable');

        // custom badge text
        const custom_badge_text_input = $('<input/>')
          .addClass(`custom-badge-input-${item.ASIN}`)
          .attr({
            type: 'text',
            name: `custom_badge[${item.ASIN}][text]`,
            placeholder: 'TOP CHOICE',
            readonly: !custom_badge_enabled,
          })
          .val(item.custom_badge.text || '')
          .on('change', (e) => {
            item.custom_badge.text = $(e.target).val();
          });

        // custom badge background
        const custom_badge_background_input = $('<input style="margin-top: 5px"/>')
          .addClass(`custom-badge-background-input-${item.ASIN}`)
          .attr({
            type: 'text',
            name: `custom_badge[${item.ASIN}][bg]`,
            readonly: !custom_badge_enabled,
            placeholder: '#F89922',
          })
          .val(item.custom_badge.bg)
          .on('change', (e) => {
            item.custom_badge.bg = $(e.target).val();
          });

        item_row.append($('<td></td>').append(custom_badge_checkbox_label).append(custom_badge_text_input).append(custom_badge_background_input));

        // ====================================
        // Product highlights
        const product_highlights = $(`<div class="product-hl" id="product-hl-${item.ASIN}" placeholder="Product highlights...">${item.product_hl || ''}</div>`);

        let edit_toggle = $(`<a><i class="fa fa-pencil-alt"></i> Edit</a>`).on('click', (e) => {
          e.preventDefault();

          item._product_hl_edit_enabled = !item._product_hl_edit_enabled;
          if (item._product_hl_edit_enabled) {
            product_highlights
              .trumbowyg({
                btns: [['viewHTML'], ['b', 'em'], ['unorderedList', 'orderedList']],
                autogrow: true,
                removeformatPasted: true,
              })
              .on('tbwchange', (e) => {
                item.product_hl = product_highlights.trumbowyg('html');
              });
            edit_toggle.addClass('button is-link mt-5px').text('Confirm');
          } else {
            product_highlights.trumbowyg('destroy');
            edit_toggle.removeClass('button is-link mt-5px').html('<i class="fa fa-pencil-alt"></i> Edit');
          }
        });

        item_row.append($('<td></td>').append(product_highlights, edit_toggle));

        // Info
        const custom_buy_btn = $('<input/>')
          .attr({
            type: 'text',
            name: 'buy_btn[' + item.ASIN + ']',
            placeholder: 'Custom buy button',
          })
          .val(item.buy_btn || '')
          .on('change', (e) => {
            item.buy_btn = $(e.target).val();
          });
        const product_info_list = $(`
          <ul>
            <li><b>Price: </b>${item.price}</li>
            <li><b>Merchant: </b>${item.merchant}</li>
            <li><b>Prime: </b>${item.prime == 'true' ? '✔Prime' : '-'}</li>
          </ul>
        `).append($('<li style="margin-top: 5px"></li>').append(custom_buy_btn));

        item_row.append($('<td></td>').append(product_info_list));

        $('#products-added-list tbody').append(item_row);
      }

      $('#products-added-list tbody tr').first().find('.button-up').prop('disabled', true);
      $('#products-added-list tbody tr').last().find('.button-down').prop('disabled', true);
    }

    // ================================
    // search products
    const search_product = {
      exists_asins: new Set(table_data.products.map((item) => item.ASIN)),
      item_page: 1,
      products: [],
      selected_count: 0,
    };

    function doSearchProducts() {
      $('#btn-search').prop('disabled', true).addClass('btn-loading');

      const keyword = $('#keywords_search').val().trim();

      if (search_product.item_page == 1) {
        setSearchStatus('searching', false);
      } else {
        setSearchStatus('searching', true);
      }

      if (search_product.item_page > 10 || !keyword) {
        return;
      }

      let data = {
        action: 'search_products',
        keywords: $('#keywords_search').val(),
        search_by: $('#search_by').val(),
        'item-page': search_product.item_page,
      };

      $.post(latat_ajax_object.ajax_url, data, function (response) {
        setSearchStatus('');
        $('#btn-search').prop('disabled', false).removeClass('btn-loading');

        response = JSON.parse(response);

        if (response.status == 'error') {
          setSearchStatus('error', false, response.message);
        }

        search_product.products = search_product.products.concat(response.products);

        if (response.products.length > 0) {
          for (const item of response.products) {
            let product_wrapper = $(`<div id="search-product-${item.ASIN}"></div>`).addClass('panel-block product-item-wrapper');

            const select_product_checkbox = $(`<input type="checkbox" value="${item.ASIN}" class="add-product-checkbox" name="select-product"/>`)
              .prop('disabled', search_product.exists_asins.has(item.ASIN))
              .on('change', (e) => {
                const checked = $(e.target).is(':checked');
                item._selected = checked;
                if (checked) {
                  search_product.selected_count += 1;
                  $(`#search-product-${item.ASIN}`).addClass('product-selected');
                } else {
                  search_product.selected_count -= 1;
                  $(`#search-product-${item.ASIN}`).removeClass('product-selected');
                }
                $('#btn-add-product').prop('disabled', !search_product.selected_count).text(`Add (${search_product.selected_count}) selected products`);
              });
            product_wrapper.append($('<div class="add-product"></div>').append(select_product_checkbox));

            // Image
            product_wrapper.append(
              $('<div></div>')
                .addClass('product-thumbnail')
                .append($('<img/>').attr({ src: item.image, width: 100, height: 100 }))
            );

            // Item title.
            product_wrapper.append($(`<div class="product-details product-title"><a href="${item.detail_page_url}" target="_blank">${item.title}</a></div>`));

            // Price
            // prettier-ignore
            product_wrapper.append(
              $('<div></div>')
              .addClass('product-details product-price')
              .append($('<b></b>').addClass('product-detail-name').text('Price:'))
              .append($('<span></span>').addClass('product-detail-value').text(item.price))
              );

            // Merchant
            // prettier-ignore
            product_wrapper.append(
              $('<div></div>')
                .addClass('product-details product-merchant-info-name')
                .append($('<b></b>').addClass('product-detail-name').text('Merchant:'))
                .append($('<span></span>').addClass('product-detail-value').text(item.merchant))
            );

            // prime
            // prettier-ignore
            if(item.prime){
              product_wrapper.append(
                $('<div></div>')
                .addClass('product-details product-merchant-info-name')
                .append($('<b style="color: #0073AA"></b>').addClass('product-detail-name').text('✔Prime'))
                );
            }
            $('#search-product-result').append(product_wrapper);
          }
        }
      }).always((response) => {
        response = JSON.parse(response);
        if (search_product.products.length < response.count && search_product.item_page < 10) {
          search_product.item_page += 1;
          setSearchStatus('loadmore_btn', true);
        }
      });
    }

    function doAddProducts() {
      $('#btn-add-product').prop('disabled', true).addClass('btn-loading');

      const selected_products = search_product.products.filter((p) => {
        if (p._selected) {
          delete p._selected;
          delete p._product_hl_edit_enabled;
          return true;
        }
        return false;
      });
      selected_products.map((item) => {
        Object.assign(item, {
          custom_badge: {
            enabled: false,
            text: '',
            color: '#f89922',
          },
          product_hl: '',
          buy_btn: '',
          custom_data: [],
        });
      });
      Object.assign(table_data, {
        products: table_data.products.concat(selected_products),
      });
      doSaveTable(true);
    }

    function setSearchStatus(status = 'init', append = false, custom_text) {
      const el = $(`<div id="search-status">Search results will be displayed here.</div>`);

      if (status == '') {
        $('#search-status').remove();
        return;
      } else if (status == 'searching') {
        $(el).addClass('searching').html('<i class="fa fa-spinner fa-pulse fa-fw" style="margin-right: 5px"></i>Loading...');
      } else if (status == 'error') {
        $(el).addClass('error').text('Error, please try again later!');
      } else if (status == 'loadmore_btn') {
        $(el).html(
          $('<button id="search-load-more" class="button">Load more</button>').on('click', () => {
            setSearchStatus('');
            doSearchProducts();
          })
        );
      }

      if (custom_text) {
        $(el).text(custom_text);
      }

      if (append) {
        $('#search-product-result').append(el);
      } else {
        $('#search-product-result').html(el);
      }

      return;
    }

    function doCloseSearchModal() {
      $('#keywords_search').val('');
      $('#btn-add-product').text('Add (0) selected products').prop('disabled', true);
      setSearchStatus('init');
      $('#search-product-modal').removeClass('is-active');
      Object.assign(search_product, {
        products: [],
        item_page: 1,
        selected_count: 0,
      });
    }

    function doResetSearchForm() {
      Object.assign(search_product, {
        products: [],
        item_page: 1,
        selected_count: 0,
      });
    }

    $('#btn-add-product').on('click', () => {
      doAddProducts();
    });

    $('#btn-clear-search').on('click', () => {
      doCloseSearchModal();
    });

    $('#btn-open-search-modal').on('click', () => {
      $('#search-product-modal').addClass('is-active');
      setSearchStatus('init');
    });

    $('#btn-search').on('click', (e) => {
      doResetSearchForm();
      doSearchProducts();
    });

    $('#btn-save-table').on('click', () => doSaveTable());

    // ================================
    // do save
    function doSaveTable(reload_on_succes = false) {
      $('#btn-save-table').prop('disabled', true).addClass('btn-loading');
      $('#btn-add-product').prop('disabled', true).addClass('btn-loading');

      const table_to_save = {
        action: 'save_table',
        post_id: table_data.post_id,
        post_title: $('#post_title').val().trim(),
        post_content: encrypt_post_content({
          display_style: $('[name="display-style"]:checked').val() ? +$('[name="display-style"]:checked').val() : 3,
          products: table_data.products,
          custom_columns: table_data.custom_columns,
        }),
      };

      $.post(latat_ajax_object.ajax_url, table_to_save, function (response) {
        response = JSON.parse(response);
        if (response.success) {
          if (reload_on_succes) {
            window.location.reload();
          } else {
            $('#btn-save-table').prop('disabled', false).removeClass('btn-loading');
            $('#btn-add-product').prop('disabled', false).removeClass('btn-loading');
            setTimeout(() => {
              $('#noti-success').hide();
            }, 1000);
          }
        } else {
          $('#btn-save-table').prop('disabled', false).removeClass('btn-loading');
          $('#btn-add-product').prop('disabled', false).removeClass('btn-loading');
          $('#noti-danger').show();
          setTimeout(() => {
            $('#noti-danger').hide();
          }, 3000);
        }
      });
    }
  }
});
