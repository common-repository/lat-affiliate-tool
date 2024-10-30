<div id="search-product-modal" class="modal">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head" style="display: initial">
      <h1 class="modal-card-title">Add products</h1>
      <section id="search-product-form" >
          <div style="display: flex">
              <div style="flex: 1">
                  <input id="keywords_search" class="input" type="text" placeholder="Search keywords or product ASINs..." value="">
              </div>
              <div style="padding: 0 5px">
                <select id="search_by" name="search_by" style="height: 33px">
                  <option value="keywords" selected>Product Title</option>
                  <option value="asin">Product ASIN</option>
                </select>
              </div>
              <div >
                <button id="btn-search" class="button">Search</button>
              </div>
          </div>
      </section>
    </header>
    <section id="search-product-result" class="modal-card-body" style="padding: 0"></section>
    <footer class="modal-card-foot">
      <button id="btn-add-product" class="button button-primary" disabled>Add selected products</button>
      <button id="btn-clear-search" class="button is-text">Cancel</button>
    </footer>
  </div>
</div>