$(document).ready(function () {
  fetchDataAndRenderChart();
});

function fetchDataAndRenderChart() {
  $.ajax({
    url: `/dashboard/form/results`,
    method: 'GET',
    success: function (data) {
      if (data.results) {
        var formTitles = extractFormTitles(data);
        var sessionCounts = extractSessionCounts(data);
        renderChart(formTitles, sessionCounts);
      }
    },
    error: function (xhr, status, error) {
      console.error('Error fetching data:', error);
    },
  });
}

function extractFormTitles(data) {
  return data.results.map(function (result) {
    return result.form_title;
  });
}

function extractSessionCounts(data) {
  return data.results.map(function (result) {
    return result.session_count;
  });
}

function renderChart(formTitles, sessionCounts) {
  var ctx = $('#formsChart')[0].getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: formTitles,
      datasets: [
        {
          label: 'Form Entries',
          data: sessionCounts,
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)',
            'rgba(99, 132, 255, 0.2)',
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(99, 132, 255, 1)',
          ],
          borderWidth: 1,
        },
      ],
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}
