@canany(['user', 'page'], [ACL_READ])
    user or page
@elsecanany(['group'], ACL_READ)
    allow
@else
    denied
@endcannot