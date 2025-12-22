<div class="row pageDetail">
  <div class="col-12">
    <div class="card">
      <div class="card-header sayfaBaslik" style="font-size:13px;">
        Rollerdeki İzinler
      </div>
      <div class="card-body" id="rollerdekiIzinler">
        <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;overflow-x:scroll;">
          <thead class="title">
            <tr>
              <th style="width: 10px">ID</th>
              <th style="width: 10px">Rol Adı</th>
              <th>İzinler</th>
              <th data-priority="1" style="width: 96px;">Düzenle</th>
            </tr>
          </thead>
          <tbody>
            @foreach($roles as $item)
              <tr class="rolePerm">
                <td class="gizli cateTd"><span class="idWrap">{{$item->id}}</span></td>
                <td class="cateTd"><div class="mobileTitle">Rol Adı:</div>{{$item->name}}</td>
                <td class="cateTd"><div class="mobileTitle address">İzinler:</div>
                  @foreach($item->permissions as $perm)
                    <span class="badge bg-danger">{{$perm->name}}</span>
                    <br>
                  @endforeach
                </td>
                <td>
                  <a href="{{ route('edit.roles.permission', [$firma->id,$item->id])}}" class="btn btn-warning btn-sm mobilBtn duzenleCat" title="Düzenle"><i class="fas fa-edit"></i></a>
                  <a href="{{ route('delete.roles.permission', [$firma->id,$item->id])}}" class="btn btn-danger btn-sm mobilBtn" id="delete" title="Sil"><i class="fas fa-trash-alt"></i></a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div> <!-- end col -->
</div> <!-- end row -->
