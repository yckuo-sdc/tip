/****** Main ******/
$(document).ready(function () {
  pageSwitch();

  /****** Event Handler Functions ******/

  // sidebar switch
  $("#sidebar li li").on("click", function () {
    var num = $(this).index();
    var content = $("#content").find(".sub-content")[num];
    $("#sidebar li li").removeClass("active");
    $(this).addClass("active");
    $("#content .sub-content").removeClass("show").hide();
    $(content).addClass("show").show();
    // push state for changing browser history
    var url = location.href;
    if (getParameterByName("mainpage", url) != null) {
      var mainpage = getParameterByName("mainpage", url);
    } else {
      var mainpage = 1;
    }
    subpage = num + 1;
    history.pushState(
      { mainpage: mainpage, subpage: subpage },
      "",
      "index.php?mainpage=" + mainpage + "&subpage=" + subpage,
    );
  });

  // tabular tab switch
  $(".tabular.menu .item, .pointing.menu .item").on("click", function () {
    var num = $(this).index();
    var content = $(".ui.attached.segment").find(".tab-content")[num];
    $(".tabular.menu .item, .pointing.menu .item").removeClass("active");
    $(this).addClass("active");
    $(".ui.attached.segment .tab-content").removeClass("show").hide();
    $(content).addClass("show").show();

    // push state for changing browser history
    var routerParameter = getRouterParameter();
    var mainpage = routerParameter.mainpage;
    var subpage = routerParameter.subpage;
    var tab = num + 1;

    history.pushState(
      { mainpage: mainpage, subpage: subpage, tab: tab },
      "",
      "/" + mainpage + "/" + subpage + "/?tab=" + tab,
    );
  });

  // query.php's component action
  $(
    ".post.event .record_content, .post.ncert .record_content, .post.contact .record_content, .post.client",
  ).on("click", ".ui.pagination.menu > .item", function (e) {
    var record = $(this).closest(".record_content");
    var page = $(this).attr("page");
    var parameter = [
      { name: "page", value: page },
      { name: "key", value: $(record).attr("key") },
      { name: "keyword", value: $(record).attr("keyword") },
      { name: "type", value: $(record).attr("type") },
      { name: "ap", value: $(record).attr("ap") },
      { name: "jsonConditions", value: $(record).attr("jsonConditions") },
    ];

    var jsonSorts = $(record).attr("jsonSorts");
    if (jsonSorts !== undefined) {
      var item = { name: "jsonSorts", value: jsonSorts };
      parameter = parameter.concat(item);
    }

    query_pagination_ajax(parameter);
    e.preventDefault();

    var routerParameter = getRouterParameter();
    var mainpage = routerParameter.mainpage;
    var subpage = routerParameter.subpage;
    var tab = routerParameter.tab;
    var taburl = "";

    if (tab != 1) {
      taburl = "/?tab=" + tab;
    }

    history.pushState(
      { mainpage: mainpage, subpage: subpage, pages: page, tab: tab },
      "",
      "/" + mainpage + "/" + subpage + "/pages/" + page + taburl,
    );
  });

  // vul_query.php's component action
  $(".post.scanResult .record_content").on(
    "click",
    ".ui.pagination.menu > .item",
    function (e) {
      var record = $(this).closest(".record_content");
      var page = $(this).attr("page");
      var data_array = [
        { name: "page", value: page },
        { name: "key", value: $(record).attr("key") },
        { name: "keyword", value: $(record).attr("keyword") },
        { name: "type", value: $(record).attr("type") },
        { name: "jsonConditions", value: $(record).attr("jsonConditions") },
        { name: "jsonStates", value: $(record).attr("jsonStates") },
      ];

      vul_query_pagination_ajax(data_array);
      e.preventDefault();

      var routerParameter = getRouterParameter();
      var mainpage = routerParameter.mainpage;
      var subpage = routerParameter.subpage;
      var tab = routerParameter.tab;
      var taburl = "";

      if (tab != 1) {
        taburl = "/?tab=" + tab;
      }

      history.pushState(
        { mainpage: mainpage, subpage: subpage, pages: page, tab: tab },
        "",
        "/" + mainpage + "/" + subpage + "/pages/" + page + taburl,
      );
    },
  );

  // network.php's component action
  $(".post.network .record_content").on(
    "click",
    ".ui.pagination.menu > .item",
    function (e) {
      var record = $(this).closest(".record_content");
      var page = $(this).attr("page");
      var data_array = [
        { name: "page", value: page },
        { name: "key", value: $(record).attr("key") },
        { name: "keyword", value: $(record).attr("keyword") },
        { name: "operator", value: $(record).attr("operator") },
        { name: "type", value: $(record).attr("type") },
        { name: "jsonConditions", value: $(record).attr("jsonConditions") },
      ];

      ips_query_pagination_ajax(data_array);
      e.preventDefault();

      var routerParameter = getRouterParameter();
      var mainpage = routerParameter.mainpage;
      var subpage = routerParameter.subpage;
      var tab = routerParameter.tab;
      var taburl = "";

      if (tab != 1) {
        taburl = "/?tab=" + tab;
      }

      history.pushState(
        { mainpage: mainpage, subpage: subpage, pages: page, tab: tab },
        "",
        "/" + mainpage + "/" + subpage + "/pages/" + page + taburl,
      );
    },
  );

  // collapse action
  $(".record_content").on("click", ".ui.list > .item a", function () {
    var icon = $(this).find("i.icon");
    var detail = $(this).parent().find(".description");
    if (detail.hasClass("show")) {
      icon.removeClass("up").addClass("down");
      detail.removeClass("show");
    } else {
      icon.removeClass("down").addClass("up");
      detail.addClass("show");
    }
  });

  // query.php's component action
  $(".post.vul_overview").on("click", ".ou_block a", function () {
    var icon = $(this).find("i.icon");
    var detail = $(this).closest(".ou_block").find(".description");
    if (detail.hasClass("show")) {
      icon.removeClass("down").addClass("right");
      detail.removeClass("show");
    } else {
      icon.removeClass("right").addClass("down");
      detail.addClass("show");
    }
  });

  // query.php's component action
  $(".post.malware .dynamiclists i.icon.caret").on("click", function () {
    var icon = $(this);
    var detail = $(this).closest(".dynamiclists").find(".foldable.card");
    if (icon.hasClass("down")) {
      icon.removeClass("down").addClass("right");
      detail.removeClass("show");
    } else {
      icon.removeClass("right").addClass("down");
      detail.addClass("show");
    }
  });

  // query.php's component action
  $(".post.client div.header > a").on("click", function (e) {
    var record = $(this).parent().siblings(".record_content");
    var label = $(this).attr("data-label");
    var icon = $(this).find("i.icon");
    var iconSiblings = $(this).siblings().find("i.icon");

    $(iconSiblings).removeClass("up down");
    if (icon.hasClass("up")) {
      var sort = "descending";
      icon.removeClass("up").addClass("down");
    } else if (icon.hasClass("down")) {
      var sort = "ascending";
      icon.removeClass("down").addClass("up");
    } else {
      var sort = "ascending";
      icon.removeClass("down").addClass("up");
    }

    var jsonSorts = {};
    jsonSorts["label"] = label;
    jsonSorts["sort"] = sort;
    jsonSorts = JSON.stringify(jsonSorts);

    var parameter = [
      { name: "page", value: 1 },
      { name: "key", value: $(record).attr("key") },
      { name: "keyword", value: $(record).attr("keyword") },
      { name: "type", value: $(record).attr("type") },
      { name: "ap", value: $(record).attr("ap") },
      { name: "jsonConditions", value: $(record).attr("jsonConditions") },
      { name: "jsonSorts", value: jsonSorts },
    ];

    $(record).attr("jsonSorts", jsonSorts);
    query_pagination_ajax(parameter);
    e.preventDefault();
  });

  // query.php's component action
  $(
    ".post.client .query_content, .post.scanResult .query_content, .post.network .query_content",
  ).on("click", "button.close", function () {
    var query_label = $(this).parent(".query_label");
    query_label.remove();
  });

  /*vul.php's component action*/
  $(".post.client i.square.icon, .post.scanResult i.square.icon").on(
    "click",
    function () {
      var type = $(this).closest(".post").attr("class").split(" ")[1];
      var selector = ".post." + type + " ";
      if (type == "client") {
        subtype = $(this).closest(".tab-content").attr("class").split(" ")[1];
        selector = selector + ".tab-content." + subtype + " ";
      }

      var key = $(selector + "#key").val();
      var keyword = $(selector + "#keyword").val();
      var keyword_text = $(selector + "#keyword option:selected").text();
      if (key !== undefined && key != "" && keyword != "") {
        var query_content = $(selector + ".query_content");
        var query_label =
          "<div class='ui grey label query_label' keyword='" +
          keyword +
          "' key='" +
          key +
          "'>" +
          keyword_text +
          " = " +
          key +
          "<button type='button' class='close'><i class='close icon'></i></button></div>";
        query_content.append(query_label);
      } else {
        alert("沒有輸入");
      }
    },
  );

  // ips_query.php's component action
  $(".post.network i.square.icon").on("click", function () {
    var type = $(this).closest(".tab-content").attr("class").split(" ")[1];
    var selector = ".post.network .tab-content." + type + " ";
    var key = $(selector + "#key").val();
    var keyword = $(selector + "#keyword").val();
    var operator = $(selector + "#operator").val();
    var keyword_text = $(selector + "#keyword option:selected").text();
    if (key !== undefined && key != "" && keyword != "" && operator != "") {
      var query_content = $(selector + ".query_content");
      var query_label =
        "<div class='ui grey label query_label' keyword='" +
        keyword +
        "' operator='" +
        operator +
        "' key='" +
        key +
        "'>" +
        keyword_text +
        " " +
        operator +
        " " +
        key +
        "<button type='button' class='close'><i class='close icon'></i></button></div>";
      query_content.append(query_label);
    } else {
      alert("沒有輸入");
    }
  });

  // bind submit of form
  $(
    ".post.event form, .post.ncert form, .post.contact form, .post.client form",
  ).on("submit", function (e) {
    var type = $(this).closest(".post").attr("class").split(" ")[1];
    if (type == "client") {
      type = $(this).closest(".tab-content").attr("class").split(" ")[1];
    }
    console.log(type);

    var parameter = { type: type, ap: "html", partial: true };
    query_ajax(parameter);
    e.preventDefault();
  });

  $(".tab-content.drip #export2csv_btn").on("click", function () {
    var parameter = { type: "drip", ap: "csv", partial: true };
    query_ajax(parameter);
  });

  $(".post.network .tab-content form").on("submit", function (e) {
    var type = $(this).closest(".tab-content").attr("class").split(" ")[1];
    console.log(type);
    var parameter = { type: type, partial: true };
    ips_query_ajax(parameter);
    e.preventDefault();
  });

  $(".post.scanResult form").on("submit", function (e) {
    var parameter = { type: "scanResult", ap: "html", partial: true };
    vul_query_ajax(parameter);
    e.preventDefault();
  });

  $(".post.scanResult #export2csv_btn").on("click", function () {
    var parameter = { type: "scanResult", ap: "csv", partial: true };
    vul_query_ajax(parameter);
  });

  /*
  $(".post.search form").on("submit", function (e) {
    var parameter = { type: "search" };
    var parameter = {
      type: "search",
      source: "form",
    };
    do_search_ajax(parameter);
    e.preventDefault();
  });
  */

  // collapse action
  $(".post.search .record_content").on("click", "div.button", function () {
    let icon = $(this).parent().find("i.icon");
    let detail = $(this).parent().find(".detail");
    if (detail.hasClass("show")) {
      icon.removeClass("minus").addClass("add");
      detail.removeClass("show");
    } else {
      icon.removeClass("add").addClass("minus");
      detail.addClass("show");
    }
  });

  // Initializing of semantic dropdown
  $(".ui.profile.dropdown").dropdown();

  // semantic progress bar
  $(".progress").progress({ showActivity: false });

  // semantic file input
  $("input:text").on("click", function () {
    $(this).parent().find("input:file").click();
  });

  $("input:file", ".ui.action.input").on("change", function (e) {
    var name = e.target.files[0].name;
    $("input:text", $(e.target).parent()).val(name);
  });

  // semantic accordion
  $(".ui.accordion").accordion();

  // semantic table
  $("table.sortable").tablesort();

  // semantic modal display
  $("#modal_btn").on("click", function () {
    $(".ui.modal").modal("show");
  });

  $(".post.event").on("click", ".button.edit", function () {
    var id = $(this).attr("key");
    event_edit_ajax(id, "edit", "event");
  });

  $(".post.event").on("click", ".button.delete", function () {
    if (confirm("是否刪除此紀錄")) {
      var id = $(this).attr("key");
      event_edit_ajax(id, "delete", "event");
    }
  });

  // semantic dismissable block
  $(".message .close").on("click", function () {
    $(this).closest(".message").transition("fade");
  });

  $(".post_cell.upload_contact #upload_Form").on("submit", function (e) {
    var selector = ".post_cell.upload_contact ";
    $.ajax({
      url: "/ajax/upload_contact/",
      type: "POST",
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData: false,
    })
      .done(function (data) {
        $(selector + ".record_content").html(data);
      })
      .fail(function (jqXHR) {
        ajax_check_user_logged_out(jqXHR);
      });

    e.preventDefault();
  });

  $(".post_cell.upload_fireeye #upload_Form").on("submit", function (e) {
    var selector = ".post_cell.upload_fireeye ";
    $.ajax({
      url: "/ajax/upload_fireeye/",
      type: "POST",
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData: false,
    })
      .done(function (data) {
        $(selector + ".record_content").html(data);
      })
      .fail(function (jqXHR) {
        ajax_check_user_logged_out(jqXHR);
      });

    e.preventDefault();
  });

  // mobile launch button
  $("#example .fixed.main.menu .item.launch").on("click", function () {
    console.log("launch");
    $("#toc.ui.left.sidebar").toggleClass("overlay visible");
  });
  // push sidebar to left
  $(".pusher, .no_pusher").on("click", function () {
    $("#toc.ui.left.sidebar").removeClass("overlay visible");
  });

  $("#sidebar a").css("text-decoration", "none");
});

/****** Custom Functions ******/

function query_ajax(parameter) {
  var type = parameter.type;
  var ap = parameter.ap;
  var page = parameter.page;
  var partial = parameter.partial;

  var client_types = ["drip", "gcb", "wsus", "antivirus", "edr"];

  if (client_types.indexOf(type) >= 0) {
    var selector = ".tab-content." + type + " ";
  } else {
    var selector = ".post." + type + " ";
  }

  if (partial) {
    var jsonConditions = [];
    $(selector + "div.query_label").each(function () {
      var item = {};
      item["keyword"] = $(this).attr("keyword");
      item["key"] = $(this).attr("key");
      jsonConditions.push(item);
    });
    jsonConditions = JSON.stringify(jsonConditions);

    // Encode a set of form elements as an array of names and values
    var input = $(selector + "form").serializeArray();
  } else {
    var jsonConditions = [];
    jsonConditions = JSON.stringify(jsonConditions);

    // Encode a set of form elements as an array of names and values
    var input = [
      { name: "key", value: "any" },
      { name: "keyword", value: "all" },
      { name: "page", value: page },
    ];
  }

  var obj = [
    { name: "jsonConditions", value: jsonConditions },
    { name: "type", value: type },
    { name: "ap", value: ap },
  ];
  input = input.concat(obj);

  // input validation
  var v1 = 0,
    v2 = 0;
  input.forEach(function (item, index, array) {
    if (item.name == "jsonConditions" && item.value == "[]") {
      v1 = 1;
    } else if (item.name != "jsonConditions" && item.value == "") {
      v2 = 1;
    }
  });
  if (v1 && v2) {
    alert("沒有輸入");
    return;
  }

  // ap='csv'
  if (ap == "csv") {
    window.location.assign("/ajax/query/?" + $.param(input));
  } else {
    // ap='html'
    input.forEach(function (item, index) {
      $(selector + ".record_content").attr(item.name, item.value);
    });

    $.ajax({
      url: "/ajax/query/",
      cache: false,
      dataType: "html",
      type: "GET",
      data: input,
    })
      .done(function (data) {
        $(selector + ".record_content").html(data);
      })
      .fail(function (jqXHR) {
        ajax_check_user_logged_out(jqXHR);
      });
  }
}

function query_pagination_ajax(parameter) {
  var type = parameter[3].value;
  var client_types = ["drip", "gcb", "wsus", "antivirus", "edr"];

  if (client_types.indexOf(type) >= 0) {
    var selector = ".tab-content." + type + " ";
  } else {
    var selector = ".post." + type + " ";
  }

  $.ajax({
    url: "/ajax/query/",
    cache: false,
    dataType: "html",
    type: "GET",
    data: parameter,
  })
    .done(function (data) {
      $(selector + ".record_content").html(data);
    })
    .fail(function (jqXHR) {
      ajax_check_user_logged_out(jqXHR);
    });
}

function vul_query_ajax(parameter) {
  var type = parameter.type;
  var ap = parameter.ap;
  var page = parameter.page;
  var partial = parameter.partial;

  var selector = ".post." + type + " ";

  if (partial) {
    var jsonConditions = [];
    $(".post.scanResult div.query_label").each(function () {
      var id = $(this).attr("title");
      var email = $(this).val();
      var item = {};
      item["keyword"] = $(this).attr("keyword");
      item["key"] = $(this).attr("key");
      jsonConditions.push(item);
    });
    jsonConditions = JSON.stringify(jsonConditions);

    var obj = $(selector + "input[name='status[]']");
    var jsonStates = {};
    jsonStates["overdue_and_unfinish"] = obj[0].checked;
    jsonStates["non_overdue_and_unfinish"] = obj[1].checked;
    jsonStates["finish"] = obj[2].checked;
    jsonStates = JSON.stringify(jsonStates);

    // Encode a set of form elements as an array of names and values
    var input = $(selector + "form")
      .find(":input")
      .not("[type=checkbox]")
      .serializeArray();
  } else {
    var jsonConditions = [];
    jsonConditions = JSON.stringify(jsonConditions);

    var jsonStates = {};
    jsonStates["overdue_and_unfinish"] = true;
    jsonStates["non_overdue_and_unfinish"] = true;
    jsonStates["finish"] = true;
    jsonStates = JSON.stringify(jsonStates);

    // Encode a set of form elements as an array of names and values
    var input = [
      { name: "key", value: "any" },
      { name: "keyword", value: "all" },
      { name: "page", value: page },
    ];
  }

  // Encode a set of form elements as an array of names and values
  var obj = [
    { name: "jsonConditions", value: jsonConditions },
    { name: "jsonStates", value: jsonStates },
    { name: "type", value: type },
    { name: "ap", value: ap },
  ];
  input = input.concat(obj);

  // input validation
  var v1 = 0,
    v2 = 0;
  input.forEach(function (item, index, array) {
    if (item.name == "jsonConditions" && item.value == "[]") {
      v1 = 1;
    } else if (item.name != "jsonConditions" && item.value == "") {
      v2 = 1;
    }
  });
  if (v1 && v2) {
    alert("沒有輸入");
    return;
  }

  // ap='csv'
  if (ap == "csv") {
    window.location.assign("/ajax/vul_query/?" + $.param(input));
  } else {
    // ap='html'
    input.forEach(function (item, index) {
      $(selector + ".record_content").attr(item.name, item.value);
    });

    $.ajax({
      url: "/ajax/vul_query/",
      cache: false,
      dataType: "html",
      type: "GET",
      data: input,
    })
      .done(function (data) {
        $(selector + ".record_content").html(data);
      })
      .fail(function (jqXHR) {
        ajax_check_user_logged_out(jqXHR);
      });
  }
}

function vul_query_pagination_ajax(data_array) {
  var type = data_array[3].value;
  var selector = ".post." + type + " ";
  $.ajax({
    url: "/ajax/vul_query/",
    cache: false,
    dataType: "html",
    type: "GET",
    data: data_array,
  })
    .done(function (data) {
      $(selector + ".record_content").html(data);
    })
    .fail(function (jqXHR) {
      ajax_check_user_logged_out(jqXHR);
    });
}

function ips_query_ajax(parameter) {
  var type = parameter.type;
  var page = parameter.page;
  var partial = parameter.partial;
  var selector = ".post.network .tab-content." + type + " ";

  if (partial) {
    var jsonConditions = [];
    $(selector + "div.query_label").each(function () {
      var item = {};
      item["keyword"] = $(this).attr("keyword");
      item["key"] = $(this).attr("key");
      item["operator"] = $(this).attr("operator");
      jsonConditions.push(item);
    });
    jsonConditions = JSON.stringify(jsonConditions);

    // Encode a set of form elements as an array of names and values
    var input = $(selector + "form").serializeArray();
  } else {
    var jsonConditions = [];
    jsonConditions = JSON.stringify(jsonConditions);

    // Encode a set of form elements as an array of names and values
    var input = [
      { name: "keyword", value: "all" },
      { name: "key", value: "any" },
      { name: "operator", value: "=" },
      { name: "page", value: page },
    ];
  }

  var obj = [
    { name: "jsonConditions", value: jsonConditions },
    { name: "type", value: type },
  ];
  input = input.concat(obj);

  // input validation
  var v1 = 0,
    v2 = 0;
  input.forEach(function (item, index, array) {
    if (item.name == "jsonConditions" && item.value == "[]") {
      v1 = 1;
    } else if (item.name != "jsonConditions" && item.value == "") {
      v2 = 1;
    }
  });
  if (v1 && v2) {
    alert("沒有輸入");
    return;
  }

  // ap='html'
  input.forEach(function (item, index) {
    $(selector + ".record_content").attr(item.name, item.value);
  });

  $(selector + ".ui.inline.loader").addClass("active");
  $(selector + ".record_content").empty();
  $.ajax({
    url: "/ajax/ips_query/",
    cache: false,
    dataType: "html",
    type: "GET",
    data: input,
  })
    .done(function (data) {
      $(selector + ".ui.inline.loader").removeClass("active");
      $(selector + ".record_content").html(data);
    })
    .fail(function (jqXHR) {
      ajax_check_user_logged_out(jqXHR);
    });
}

function ips_query_pagination_ajax(data_array) {
  var type = data_array[4].value;
  var selector = ".post.network .tab-content." + type + " ";
  $(selector + ".ui.inline.loader").addClass("active");
  $(selector + ".record_content").empty();
  $.ajax({
    url: "/ajax/ips_query/",
    cache: false,
    dataType: "html",
    type: "GET",
    data: data_array,
  })
    .done(function (data) {
      $(selector + ".ui.inline.loader").removeClass("active");
      $(selector + ".record_content").html(data);
    })
    .fail(function (jqXHR) {
      ajax_check_user_logged_out(jqXHR);
    });
}

function do_search_ajax(parameter) {
  let type = parameter.type;
  let source = parameter.source;
  let selector = ".post." + type + " ";
  let data;

  console.log(source)

  if (source == "form") {
    data = $(selector + "form").serializeArray();
  } else if (source == "url") {
    let query = parameter.query;
    data = [{"name": "query", "value": query}];
    $(selector).find("input[name='query']").val(query);
  }

  $(selector + ".ui.inline.loader").addClass("active");
  $.ajax({
    url: "/ajax/do_search/",
    cache: false,
    dataType: "html",
    type: "POST",
    data: data,
  })
    .done(function (data) {
      $(selector + ".ui.inline.loader").removeClass("active");
      $(selector + ".record_content").html(data);
      $(".ui.accordion").accordion("refresh");
    })
    .fail(function (jqXHR) {
      ajax_check_user_logged_out(jqXHR);
    });
}

function fetch_data_ajax(type) {
  var fetch_url = "/ajax/fetch_" + type + "/";
  $(".ui.inline.loader").addClass("active");
  $.ajax({
    url: fetch_url,
    cache: false,
    dataType: "html",
    type: "GET",
  })
    .done(function (data) {
      $(".ui.inline.loader").removeClass("active");
      $(".fetch_status").html(data);
    })
    .fail(function (jqXHR) {
      ajax_check_user_logged_out(jqXHR);
    });
}

// insert values form database into form inputs
function event_edit_ajax(id, action, category) {
  var selector = ".ui.modal ";

  switch (true) {
    case action == "edit" && category == "event":
      $.ajax({
        url: "/ajax/event_edit/",
        cache: false,
        dataType: "json",
        type: "get",
        data: { id: id, action: action, category: category },
      })
        .done(function (data) {
          console.log(data);
          var inputs = $(selector + "form").find(":input");
          var len = inputs.length;
          for (var i = 0; i < len; i++) {
            var name = inputs[i].name;
            var localName = inputs[i].localName;
            if (name != "submit") {
              $(selector + localName + "[name=" + name + "]").val(data[name]);
            }
          }
        })
        .fail(function (jqXHR) {
          ajax_check_user_logged_out(jqXHR);
        });

      $(selector)
        .modal({
          closable: false,
          onDeny: function () {},
          onApprove: function () {
            // alert('Approved!');
            // return false;
            $(selector + "form").submit();
          },
        })
        .modal("show");
      break;
    case action == "delete" && category == "event":
      $.ajax({
        url: "/ajax/event_edit/",
        cache: false,
        dataType: "html",
        type: "GET",
        data: { id: id, action: action, category: category },
      })
        .done(function (data) {
          history.go(0);
        })
        .fail(function (jqXHR) {
          ajax_check_user_logged_out(jqXHR);
        });
      break;
    case action == "edit" && category == "ldap_computer":
      $.ajax({
        url: "/ajax/event_edit/",
        cache: false,
        dataType: "json",
        type: "get",
        data: { id: id, action: action, category: category },
      })
        .done(function (data) {
          console.log(data);
          $(selector)
            .modal({
              closable: false,
              onDeny: function () {},
              onApprove: function () {
                $(selector + "form").submit();
              },
            })
            .modal("show");
        })
        .fail(function (jqXHR) {
          ajax_check_user_logged_out(jqXHR);
        });
      break;
  }
}

function ajax_check_user_logged_out(jqXHR) {
  if (jqXHR.status == "401") {
    alert("已逾時 請重新登入!");
  } else {
    alert("Ajax failed");
  }
}

// switch webpage by url
function pageSwitch() {
  var routerParameter = getRouterParameter();
  var mainpage = routerParameter.mainpage;
  var subpage = routerParameter.subpage;
  var query = routerParameter.query;

  switch (true) {
    case mainpage == "nics" && subpage == "search":
      if (query === "") {
        return;
      }
      var parameter = {
        type: "search",
        source: "url",
        query: query,
      };
      do_search_ajax(parameter);
      break;
    default:
      break;
  }

}

function getRouterParameter() {
  var url = location.href;
  var pathArray = location.pathname.split("/");
  var mainpage = pathArray[1] !== undefined ? pathArray[1] : "info";
  var subpage = pathArray[2] !== undefined ? pathArray[2] : "";
  var thirdpage = pathArray[3] !== undefined ? pathArray[3] : "";
  var query = getParameterByName("query", url) != null ? getParameterByName("query", url) : "";
  var page = 1;
  var tab = getParameterByName("tab", url) != null ? getParameterByName("tab", url) : 1;

  if (thirdpage == "pages" && pathArray[4] !== undefined) {
    page = pathArray[4];
  }

  var routerParameter = {};
  routerParameter["mainpage"] = mainpage;
  routerParameter["subpage"] = subpage;
  routerParameter["query"] = query
  routerParameter["page"] = page;
  routerParameter["tab"] = tab;

  console.log(routerParameter);
  return routerParameter;
}

function getParameterByName(name, url) {
  if (!url) url = location.href;
  name = name.replace(/[\[\]]/g, "\\$&");
  var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
    results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return "";
  return decodeURIComponent(results[2].replace(/\+/g, " "));
}
