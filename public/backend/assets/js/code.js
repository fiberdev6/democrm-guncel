$(function(){
  $(document).on('click','#delete',function(e){
      e.preventDefault();
      var link = $(this).attr("href");


                Swal.fire({
                  title: 'Silmek istediğinize emin misiniz?',
                  text: "Veri silinsin mi?",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Sil!',
                  cancelButtonText: 'İptal'
                }).then((result) => {
                  if (result.isConfirmed) {
                    window.location.href = link
                    Swal.fire(
                      'Silindi!',
                      'Veri başarıyla silindi.',
                      'success'
                    )
                  }
                }) 


  });

});
