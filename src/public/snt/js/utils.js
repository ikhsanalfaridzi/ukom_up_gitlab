// Generate Datatable
function generate_datatables(tableId, url, dom = "<Bf<t>lip>") {
  table = tableId.DataTable({
    dom: dom,
    processing: true,
    serverSide: true,
    destroy: true,
    searchDelay: 300,
    lengthMenu: [10, 25, 100, 1000],
    autoWidth: false,
    buttons: [
      {
        extend: "copy",
        exportOptions: {
          columns: ":not(.no-export)",
        },
      },
      {
        extend: "print",
        exportOptions: {
          columns: ":not(.no-export)",
        },
      },
      {
        extend: "excel",
        exportOptions: {
          columns: ":not(.no-export)",
        },
      },
    ],
    ajax: {
      url: url,
      type: "GET",
      dataType: "json",
    },
    columnDefs: [
      {
        targets: "_all",
        orderable: false,
      },
      {
        target: [-1],
        orderable: false,
        className: "text-center no-export",
      },
    ],
  });
}

// Generate Summernote
function generate_summernote(field) {
  field.summernote({
    height: 150,
    focus: true,
    toolbar: [["style", ["bold", "italic", "underline", "clear"]]],
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

function generate_recap_table(tableElement, tableId, url, dom, aspects) {
  // Initialize Bootstrap Popover
  const popoverTriggerList = document.querySelectorAll(
    '[data-bs-toggle="popover"]'
  );
  const popoverList = [...popoverTriggerList].map(
    (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl)
  );

  // Create Table
  let tableHtml = `
      <table class="table table-bordered" id="${tableId}">
          <thead>
              <tr>
                  <th class="text-center align-middle">Ranking</th>
                  <th class="text-center align-middle" style="min-width: 300px; white-space: nowrap;">Nama Lengkap</th>
                  <th class="text-center align-middle">Pangkat</th>
                  <th class="text-center align-middle" style="min-width: 200px; white-space: nowrap;">Umur</th>
                  ${aspects
                    .map(
                      (aspect, index) => `
                      <th
                          class="text-center"
                          data-bs-toggle="popover"
                          data-bs-title="Pertanyaan"
                          data-bs-content="${aspect.question}">
                          P${index + 1}
                      </th>
                  `
                    )
                    .join("")}
                  <th class="text-center">Total Skor</th>
                  <th class="text-center">Hasil Akhir</th>
                  <th class="text-center">Kategori</th>
                  <th class="text-center"></th>
              </tr>
          </thead>
      </table>
  `;

  // Render Table to Target Element
  $(`#${tableElement}`).html(tableHtml);

  let table = $(`#${tableId}`);

  // Generate Datatables
  generate_datatables(table, url, dom);
}
