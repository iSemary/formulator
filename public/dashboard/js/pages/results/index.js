$(document).on('click', '.view-details', function () {
  var url = $(this).data('url');
  $.ajax({
    url: url,
    method: 'GET',
    success: function (response) {
      var html = "";
      html += '<p><strong><i class="fas fa-map-marker-alt"></i> IP:</strong> ' + response.session.details.ip + '</p>';
      html += '<p><strong><i class="fas fa-laptop"></i> User Agent:</strong> ' + response.session.details.agent + '</p>';
      html += '<p><strong><i class="fas fa-calendar-alt"></i> Created At:</strong> ' + response.session.details.created_at.date + '</p>';
      html += '<hr/><h4>Results:</h4>';
      // Loop through the results and append them to the modal body
      $.each(response.session.results, function(index, result) {
        html += '<p><strong><i class="fas fa-question"></i> ' + result.question_title + '</strong><br/> <i class="fas fa-pen"></i> ' + result.answer + '</p>';
      });
      $('#modalContent').html(html);
      $('#detailsModal').modal('show');
    },
    error: function (xhr, status, error) {
      $('#modalContent').html(
        '<p>An error occurred while fetching details. Please try again.</p>'
      );
      $('#detailsModal').modal('show');
    },
  });
});