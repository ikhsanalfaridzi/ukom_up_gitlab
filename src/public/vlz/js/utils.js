/*
Author: Kevin Kurnia Wikarta
Version: 1.0.0
Website: https://kevkw.id/
Contact: kevinkurnia1232@gmail.com
File: Js Utilities
*/

// Generate Datatable (wrapper for views using old convention)
function generate_datatables(table, url, dom) {
  let tableId = $(table).attr('id');
  let columns = [];
  $(table)
    .find("thead th")
    .each(function (index) {
      // Posisi array dari respons server: data[index]
      columns.push({ data: index });
    });
  generateDataTable(tableId, url, { columns: columns, buttons: [] }, dom);
}

// Generate recap table with dynamic columns from aspects
function generate_recap_table(containerId, tableId, url, dom, aspects) {
  let container = $("#" + containerId);
  container.empty();

  let html = '<table class="table table-bordered" id="' + tableId + '"><thead><tr>';
  html += '<th class="text-center">No</th>';
  html += '<th class="text-center">Nama</th>';
  html += '<th class="text-center">Pangkat</th>';
  html += '<th class="text-center">Usia</th>';
  aspects.forEach(function (a, i) {
    html += '<th class="text-center" title="' + a.question.replace(/"/g, '&quot;') + '">P' + (i + 1) + '</th>';
  });
  html += '<th class="text-center">Nilai</th>';
  html += '<th class="text-center">Nilai Akhir</th>';
  html += '<th class="text-center">Kategori</th>';
  html += '<th class="text-center"></th>';
  html += '</tr></thead></table>';

  container.append(html);

  let columns = [
    { data: 0 },
    { data: 1 },
    { data: 2 },
    { data: 3 },
  ];
  for (let i = 0; i < aspects.length; i++) {
    columns.push({ data: 4 + i });
  }
  columns.push({ data: 4 + aspects.length });
  columns.push({ data: 5 + aspects.length });
  columns.push({ data: 6 + aspects.length });
  columns.push({ data: 7 + aspects.length, orderable: false });

  generateDataTable(tableId, url, { columns: columns, buttons: [] }, dom);
}

// Generate Datatable
function generateDataTable(tableId, url, options, dom = "<Bf<t>lip>") {
  let table = $("#" + tableId).DataTable({
    dom: dom,
    processing: true,
    serverSide: true,
    orderCellsTop: true,
    fixedHeader: true,
    ordering: false,
    autoWidth: false,
    bInfo: false,
    search: { return: true },
    ajax: {
      url: url,
      type: "POST",
      data: function (d) {
        return { ...d };
      },
    },
    pageLength: options.pageLength || 10,
    columns: options.columns,
    buttons: options.buttons,
    drawCallback: function () {
      updateFooter(tableId);
    },
  });

  table.on("preXhr.dt", function () {
    showLoading();
  });

  table.on("xhr.dt", function () {
    hideLoading();
  });

  table.on("processing.dt", function (e, settings, processing) {
    processing ? showLoading() : hideLoading();
  });
}

function updateFooter(tableId) {
  let table = $("#" + tableId).DataTable();
  let pageInfo = table.page.info();

  let totalPages = Math.ceil(pageInfo.recordsDisplay / pageInfo.length);
  let paginationHtml = createPaginationHtml(pageInfo.page, totalPages);

  // Cleanup default pagination
  $("#" + tableId + "_length").remove();
  $("#" + tableId + "_paginate").remove();

  // Update custom pagination and showing entries
  $("#dt-pagination").html(paginationHtml);

  let showingText;

  if (pageInfo.recordsDisplay === 0) {
    showingText = `${pageInfo.start} - ${pageInfo.end} dari ${pageInfo.recordsDisplay}`;
  } else {
    showingText = `${pageInfo.start + 1} - ${pageInfo.end} dari ${
      pageInfo.recordsDisplay
    }`;
  }

  $("#dt-showing-entries").text(showingText);

  // Attach new pagination event
  attachPaginationEvents(tableId);
}

function createPaginationHtml(currentPage, totalPages) {
  let paginationHtml = '<ul class="pagination">';

  paginationHtml += `<li class="page-item ${
    currentPage === 0 ? "disabled" : ""
  }" data-page="${currentPage - 1}">
      <a class="page-link">&laquo;</a>
  </li>`;

  paginationHtml += `<li class="page-item ${
    currentPage >= totalPages - 1 ? "disabled" : ""
  }" data-page="${currentPage + 1}">
      <a class="page-link">&raquo;</a>
  </li>`;

  paginationHtml += "</ul>";
  return paginationHtml;
}

function attachPaginationEvents(tableId) {
  const table = $("#" + tableId).DataTable();

  $("#dt-pagination .page-item").on("click", function () {
    if ($(this).hasClass("disabled")) return;
    const page = $(this).data("page");
    table.page(page).draw("page");
  });

  $("#dt-entries-select").on("change", function () {
    const newLength = $(this).val();
    table.page.len(newLength).draw();
  });
}

// Handle Ketika foreign key
function delete_data(name, url, token, tableId) {
  Swal.fire({
    title: "Apakah anda yakin?",
    text: "Anda akan menghapus " + name + ".",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#405189",
    cancelButtonColor: "#f06548",
    confirmButtonText: "Delete",
    cancelButtonText: "Cancel",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: url,
        type: "POST",
        data: {
          _token: token,
        },
        success: function (response) {
          showAlert("Terhapus!", name + " telah dihapus.", "success");

          $("#" + tableId)
            .DataTable()
            .ajax.reload(null, false);
        },
        error: function (xhr, status, error) {
          showAlert(
            "Error!",
            "Terjadi kesalahan ketika menghapus data.",
            "error"
          );
        },
      });
    }
  });
}

function getSelect2Value(element, url, selectedValue = "") {
  // Clear current options and set to loading
  $(element)
    .empty()
    .append(new Option("Loading...", "", true, true))
    .trigger("change");

  // Fetch data from the provided URL
  $.ajax({
    url: url,
    type: "GET",
    dataType: "json",
    success: function (data) {
      // Clear the loading option
      $(element).empty();

      // Add a placeholder option
      $(element).append(new Option("Please select...", "", true, false));

      // Populate the dropdown with new options
      $.each(data, function (index, item) {
        let isSelected = selectedValue && selectedValue == item.id;
        $(element).append(
          new Option(item.text, item.id, isSelected, isSelected)
        );
      });

      // Trigger change event for Select2 initialization
      $(element).trigger("change");
    },
    error: function () {
      // Handle errors and reset the dropdown
      $(element)
        .empty()
        .append(new Option("Error loading data", "", true, false))
        .trigger("change");
    },
  });
}

function showAlert(title, message, status = "success") {
  Swal.fire({
    position: "center",
    icon: status,
    title: title,
    text: message,
    showConfirmButton: false,
    timer: 3000,
  });
}

function getToast() {
  let toastStatus = sessionStorage.getItem("toastStatus");
  let toastMessage = sessionStorage.getItem("toastMessage");

  if (toastStatus && toastMessage) {
    return {
      status: toastStatus,
      message: toastMessage,
    };
  } else {
    return false;
  }
}

function setToast(status = "success", message) {
  sessionStorage.setItem("toastStatus", status);
  sessionStorage.setItem("toastMessage", message);
}

function removeToast() {
  sessionStorage.removeItem("toastStatus");
  sessionStorage.removeItem("toastMessage");
}

function showToast(status = "success", message) {
  let responseMessage;
  let backgroundStyle;

  switch (status) {
    case "success":
      responseMessage = `<i class="ri-checkbox-circle-line fs-5 align-middle"></i> <span class="fs-6 align-middle">${message}</span>`;
      backgroundStyle = "#0ab39c";
      break;
    case "error":
      responseMessage = `<i class="ri-close-circle-line fs-5 align-middle"></i> <span class="fs-6 align-middle">${message}</span>`;
      backgroundStyle = "#f06548";
      break;
    default:
      responseMessage = `<i class="ri-information-line fs-5 align-middle"></i> <span class="fs-6 align-middle">${message}</span>`;
      backgroundStyle = "#f7b84b";
  }

  Toastify({
    text: responseMessage,
    duration: 5000,
    newWindow: true,
    close: true,
    gravity: "top",
    position: "right",
    stopOnFocus: true,
    escapeMarkup: false,
    style: {
      background: backgroundStyle,
    },
  }).showToast();
}

function redirect(url, type) {
  if (typeof type === "undefined") type = "_self";

  window.open(url, type);
}

function booleanInteger(value, trueValue = "Ya", falseValue = "Tidak") {
  return value === "1" ? trueValue : falseValue;
}

function showIcon(iconName, sizeClass = "fs-2") {
  return `<i class="${iconName} ${sizeClass}"></i>`;
}

function verifiedLabel(status) {
  if (status === "0") {
    return `<span class="badge bg-warning"><i class="ri-refresh-line fs-17 align-middle"></i> Menunggu Verifikasi</span>`;
  } else if (status === "1") {
    return `<span class="badge bg-success"><i class="ri-checkbox-circle-line fs-17 align-middle"></i> Diterima</span>`;
  } else {
    return `<span class="badge bg-danger"><i class="ri-close-circle-line fs-17 align-middle"></i> Ditolak</span>`;
  }
}

function detailButton(id, data, ...params) {
  // Delete null or undefined parameter
  let filteredParams = params.filter(
    (param) => param !== null && param !== undefined
  );

  // If params exists, seperate with "/"
  let paramString = filteredParams.length ? filteredParams.join("/") + "/" : "";

  return `<a href="${
    site_url + uri_name + "/detail/" + paramString + id
  }" class="fw-bold">${data}</a>`;
}

function handleDropdownChange(
  dependentSelector,
  triggerSelector,
  apiUrl,
  selectedValue = ""
) {
  function updateDropdown() {
    let value = $(dependentSelector).val();

    if (value !== "") {
      let url = apiUrl + value;
      $(triggerSelector).prop("disabled", false);

      getSelect2Value(triggerSelector, url, selectedValue);
    } else {
      $(triggerSelector).val("").trigger("change");
      $(triggerSelector).prop("disabled", true);
    }
  }

  // Initialize on page load
  updateDropdown();

  // Add on change event listener
  $(dependentSelector).on("change", updateDropdown);
}

function initDownloadButton(selector, url, fileName) {
  $("#" + selector).on("click", function (e) {
    e.preventDefault();

    $.ajax({
      url: url,
      method: "GET",
      xhrFields: {
        responseType: "blob",
      },
      success: function (data, status, xhr) {
        let blob = new Blob([data], {
          type: xhr.getResponseHeader("Content-Type"),
        });

        let url = window.URL.createObjectURL(blob);

        let a = document.createElement("a");
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);

        showToast("success", "File Berhasil Diunduh!");
      },
      error: function (err) {
        console.log(err);
        showAlert(
          "Error!",
          err.responseJSON?.message || "File Tidak Ditemukan!",
          err.responseJSON?.status || "error"
        );
      },
    });
  });
}

function getRandomColor() {
  return `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(
    Math.random() * 255
  )}, ${Math.floor(Math.random() * 255)}, 0.6)`;
}

function generateBarChart(chartId, label, values) {
  const backgroundColors = values.map(() => getRandomColor());
  const borderColors = backgroundColors.map((color) =>
    color.replace("0.6", "1")
  );

  const ctx = document.getElementById(chartId).getContext("2d");

  const datasets = values.map((item, index) => ({
    label: item.name,
    data: [item.total],
    backgroundColor: backgroundColors[index],
    borderColor: borderColors[index],
    borderWidth: 1,
  }));

  Chart.register(ChartDataLabels);

  const config = {
    type: "bar",
    data: {
      labels: ["Total"],
      datasets: datasets,
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "top",
        },
        title: {
          display: true,
          text: label,
        },
        datalabels: {
          anchor: "end",
          align: "end",
          formatter: (value) => value + " Orang",
          font: {
            weight: "bold",
          },
          color: "#000",
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  };

  new Chart(ctx, config);
}

function initTableDownloadButton(selector, url) {
  $(document)
    .off("click", "#" + selector)
    .on("click", "#" + selector, function (e) {
      e.preventDefault();

      let fileName = $(this).data("file-name");
      let employeeNumber = $(this).data("employee-number");
      let decodedUrl = decodeURIComponent(url);
      let newUrl =
        decodedUrl.replace("{employee_number}", employeeNumber) + fileName;

      $.ajax({
        url: newUrl,
        method: "GET",
        xhrFields: {
          responseType: "blob",
        },
        success: function (data, status, xhr) {
          var blob = new Blob([data], {
            type: xhr.getResponseHeader("Content-Type"),
          });

          var url = window.URL.createObjectURL(blob);

          var a = document.createElement("a");
          a.href = url;
          a.download = fileName;
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          window.URL.revokeObjectURL(url);

          showToast("success", "File Berhasil Diunduh!");
        },
        error: function (err) {
          console.log(err);
          showAlert(
            "Error!",
            err.responseJSON?.message || "File Tidak Ditemukan!",
            err.responseJSON?.status || "error"
          );
        },
      });
    });
}
