All vendor ```css``` files are isolated, meaning all classes are prefixed with ```#legalforms-plugin```. This is done using less:

```
#legalforms-plugin {
  @import (less) 'filename.css'
}
```

Some classes should not be prefixed as their styling will break:
- ```body``` and ```html``` selectors
- Bootstraps ```.tooltip``` and ```.popover``` classes.

Prefixing everything is less than ideal... For Bootstrap maybe use https://github.com/sneas/bootstrap-sass-namespace in the future.
