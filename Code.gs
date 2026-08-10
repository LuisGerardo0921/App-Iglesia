function doGet(e) {
  var template = HtmlService.createTemplateFromFile('Index');
  return template.evaluate()
    .setTitle('Las Casas de Mi Amistad')
    .addMetaTag('viewport', 'width=device-width, initial-scale=1.0')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

function include(filename) {
  return HtmlService.createHtmlOutputFromFile(filename).getContent();
}

// CONEXIÓN ROBUSTA A GOOGLE SHEETS
function getSpreadsheet() {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    if (ss) return ss;
  } catch (e) {}

  var props = PropertiesService.getScriptProperties();
  var storedId = props.getProperty('SPREADSHEET_ID');
  if (storedId) {
    try {
      var ssStored = SpreadsheetApp.openById(storedId);
      if (ssStored) return ssStored;
    } catch (e) {}
  }

  try {
    var newSs = SpreadsheetApp.create('Las Casas de Mi Amistad - BD');
    props.setProperty('SPREADSHEET_ID', newSs.getId());
    setupInitialDataForSs(newSs);
    return newSs;
  } catch (err) {
    throw new Error("No se pudo acceder a la Hoja de Cálculo: " + err.message);
  }
}

function getSheetRows(sheetName) {
  var ss = getSpreadsheet();
  var sheet = ss.getSheetByName(sheetName);
  if (!sheet) return [];
  var data = sheet.getDataRange().getValues();
  if (data.length <= 1) return [];
  var headers = data[0];
  var rows = [];
  for (var i = 1; i < data.length; i++) {
    var row = data[i];
    var obj = {};
    for (var j = 0; j < headers.length; j++) {
      obj[String(headers[j]).trim()] = row[j] !== undefined ? row[j] : '';
    }
    rows.push(obj);
  }
  return rows;
}

function writeSheetRows(sheetName, headers, objectsList) {
  var ss = getSpreadsheet();
  writeRowsToSs(ss, sheetName, headers, objectsList);
}

function writeRowsToSs(ss, sheetName, headers, objectsList) {
  var sheet = ss.getSheetByName(sheetName);
  if (!sheet) sheet = ss.insertSheet(sheetName);
  sheet.clear();
  var matrix = [headers];
  for (var i = 0; i < objectsList.length; i++) {
    var obj = objectsList[i];
    var row = [];
    for (var j = 0; j < headers.length; j++) {
      row.push(obj[headers[j]] !== undefined ? obj[headers[j]] : '');
    }
    matrix.push(row);
  }
  sheet.getRange(1, 1, matrix.length, headers.length).setValues(matrix);
  sheet.getRange(1, 1, 1, headers.length).setFontWeight("bold").setBackground("#EBE1D7");
}

function verifyPin(pin) {
  if (!pin) return { success: false, message: 'Ingresa un PIN válido.' };
  var userPin = String(pin).trim();

  // Fallback para Admin (1234)
  if (userPin === '1234') {
    return {
      success: true,
      user: { nombre: 'Administrador Principal', tipo: 'admin', casaAsignada: '', pin: '1234' }
    };
  }

  var usuarios = getSheetRows('Usuarios');
  for (var i = 0; i < usuarios.length; i++) {
    var u = usuarios[i];
    if (String(u.PIN).trim() === userPin && String(u.Estado).toLowerCase() === 'activo') {
      return { success: true, user: { nombre: u.Nombre, tipo: u.Tipo, casaAsignada: u.CasaAsignada, pin: u.PIN } };
    }
  }
  return { success: false, message: 'PIN incorrecto o inactivo.' };
}

function isAdmin(pin) {
  var res = verifyPin(pin);
  return res.success && String(res.user.tipo).toLowerCase() === 'admin';
}

function getPublicData() {
  var casas = getSheetRows('Casas');
  var integrantes = getSheetRows('Integrantes');
  var materiales = getSheetRows('Materiales');
  var conteo = {};
  integrantes.forEach(function(m) {
    if (String(m.Estado).toLowerCase() === 'activo') {
      var id = String(m.CasaAsignada);
      conteo[id] = (conteo[id] || 0) + 1;
    }
  });
  var casasPublicas = casas.filter(c => String(c.Estado).toLowerCase() === 'activo').map(c => ({
    id: c.ID, nombre: c.Nombre, direccion: c.Direccion, anfitrion: c.Anfitrion,
    facilitador: c.Facilitador, telefono: c.Telefono, dia: c.Dia, horario: c.Horario,
    totalIntegrantes: conteo[String(c.ID)] || 0
  }));
  var materialesPublicos = materiales.filter(m => String(m.Estado).toLowerCase() === 'activo').map(m => ({
    id: m.ID, titulo: m.Titulo, descripcion: m.Descripcion, fecha: m.Fecha, enlaceDrive: m.EnlaceDrive
  }));
  return { casas: casasPublicas, materiales: materialesPublicos };
}

function getHouseDetailForUser(pin) {
  var auth = verifyPin(pin);
  if (!auth.success) throw new Error(auth.message);
  var user = auth.user;
  var casas = getSheetRows('Casas');
  var integrantes = getSheetRows('Integrantes');
  var miCasa = casas.find(c => String(c.ID) === String(user.casaAsignada));
  if (!miCasa) return { user: user, casa: null, integrantes: [] };
  var misIntegrantes = integrantes.filter(m => String(m.CasaAsignada) === String(miCasa.ID) && String(m.Estado).toLowerCase() === 'activo').map(m => ({
    id: m.ID, nombre: m.Nombre, telefono: m.Telefono, fechaRegistro: m.FechaRegistro
  }));
  return { user: user, casa: miCasa, integrantes: misIntegrantes };
}

function adminGetAllData(pin) {
  if (!isAdmin(pin)) throw new Error("Acceso denegado: Se requiere PIN de administrador.");
  return {
    casas: getSheetRows('Casas'),
    integrantes: getSheetRows('Integrantes'),
    materiales: getSheetRows('Materiales'),
    usuarios: getSheetRows('Usuarios')
  };
}

function adminSaveCasa(pin, casaData) {
  if (!isAdmin(pin)) throw new Error("Acceso denegado.");
  var casas = getSheetRows('Casas');
  var headers = ['ID', 'Nombre', 'Direccion', 'Anfitrion', 'Facilitador', 'Telefono', 'Dia', 'Horario', 'Estado'];
  if (casaData.id) {
    for (var i = 0; i < casas.length; i++) {
      if (String(casas[i].ID) === String(casaData.id)) {
        casas[i].Nombre = casaData.nombre; casas[i].Direccion = casaData.direccion;
        casas[i].Anfitrion = casaData.anfitrion; casas[i].Facilitador = casaData.facilitador;
        casas[i].Telefono = casaData.telefono; casas[i].Dia = casaData.dia;
        casas[i].Horario = casaData.horario; casas[i].Estado = casaData.estado || 'Activo';
        break;
      }
    }
  } else {
    casas.push({ ID: 'C' + String(new Date().getTime()).slice(-5), Nombre: casaData.nombre, Direccion: casaData.direccion, Anfitrion: casaData.anfitrion, Facilitador: casaData.facilitador, Telefono: casaData.telefono, Dia: casaData.dia, Horario: casaData.horario, Estado: casaData.estado || 'Activo' });
  }
  writeSheetRows('Casas', headers, casas);
  return { success: true, message: 'Casa guardada con éxito.' };
}

function adminDeleteCasa(pin, casaId) {
  if (!isAdmin(pin)) throw new Error("Acceso denegado.");
  var casas = getSheetRows('Casas').filter(c => String(c.ID) !== String(casaId));
  writeSheetRows('Casas', ['ID', 'Nombre', 'Direccion', 'Anfitrion', 'Facilitador', 'Telefono', 'Dia', 'Horario', 'Estado'], casas);
  return { success: true, message: 'Casa eliminada.' };
}

function adminSaveIntegrante(pin, data) {
  if (!isAdmin(pin)) throw new Error("Acceso denegado.");
  var list = getSheetRows('Integrantes');
  var headers = ['ID', 'Nombre', 'Telefono', 'CasaAsignada', 'FechaRegistro', 'Estado'];
  if (data.id) {
    for (var i = 0; i < list.length; i++) {
      if (String(list[i].ID) === String(data.id)) {
        list[i].Nombre = data.nombre; list[i].Telefono = data.telefono;
        list[i].CasaAsignada = data.casaAsignada; list[i].Estado = data.estado || 'Activo';
        break;
      }
    }
  } else {
    var hoy = Utilities.formatDate(new Date(), Session.getScriptTimeZone(), "yyyy-MM-dd");
    list.push({ ID: 'I' + String(new Date().getTime()).slice(-5), Nombre: data.nombre, Telefono: data.telefono, CasaAsignada: data.casaAsignada, FechaRegistro: hoy, Estado: data.estado || 'Activo' });
  }
  writeSheetRows('Integrantes', headers, list);
  return { success: true, message: 'Integrante guardado.' };
}

function adminDeleteIntegrante(pin, id) {
  if (!isAdmin(pin)) throw new Error("Acceso denegado.");
  var list = getSheetRows('Integrantes').filter(m => String(m.ID) !== String(id));
  writeSheetRows('Integrantes', ['ID', 'Nombre', 'Telefono', 'CasaAsignada', 'FechaRegistro', 'Estado'], list);
  return { success: true, message: 'Integrante eliminado.' };
}

function adminSaveMaterial(pin, data) {
  if (!isAdmin(pin)) throw new Error("Acceso denegado.");
  var list = getSheetRows('Materiales');
  var headers = ['ID', 'Titulo', 'Descripcion', 'Fecha', 'EnlaceDrive', 'Estado'];
  if (data.id) {
    for (var i = 0; i < list.length; i++) {
      if (String(list[i].ID) === String(data.id)) {
        list[i].Titulo = data.titulo; list[i].Descripcion = data.descripcion;
        list[i].Fecha = data.fecha; list[i].EnlaceDrive = data.enlaceDrive; list[i].Estado = data.estado || 'Activo';
        break;
      }
    }
  } else {
    list.push({ ID: 'M' + String(new Date().getTime()).slice(-5), Titulo: data.titulo, Descripcion: data.descripcion, Fecha: data.fecha, EnlaceDrive: data.enlaceDrive, Estado: data.estado || 'Activo' });
  }
  writeSheetRows('Materiales', headers, list);
  return { success: true, message: 'Material publicado.' };
}

function adminDeleteMaterial(pin, id) {
  if (!isAdmin(pin)) throw new Error("Acceso denegado.");
  var list = getSheetRows('Materiales').filter(m => String(m.ID) !== String(id));
  writeSheetRows('Materiales', ['ID', 'Titulo', 'Descripcion', 'Fecha', 'EnlaceDrive', 'Estado'], list);
  return { success: true, message: 'Material eliminado.' };
}

function adminSaveUsuario(pin, data) {
  if (!isAdmin(pin)) throw new Error("Acceso denegado.");
  var list = getSheetRows('Usuarios');
  var headers = ['Nombre', 'Tipo', 'CasaAsignada', 'PIN', 'Estado'];
  var found = false;
  for (var i = 0; i < list.length; i++) {
    if (String(list[i].PIN) === String(data.pinOriginal || data.pin)) {
      list[i].Nombre = data.nombre; list[i].Tipo = data.tipo;
      list[i].CasaAsignada = data.casaAsignada || ''; list[i].PIN = data.pin; list[i].Estado = data.estado || 'Activo';
      found = true; break;
    }
  }
  if (!found) list.push({ Nombre: data.nombre, Tipo: data.tipo, CasaAsignada: data.casaAsignada || '', PIN: data.pin, Estado: data.estado || 'Activo' });
  writeSheetRows('Usuarios', headers, list);
  return { success: true, message: 'PIN guardado.' };
}

function adminDeleteUsuario(pin, pinToDelete) {
  if (!isAdmin(pin)) throw new Error("Acceso denegado.");
  var list = getSheetRows('Usuarios').filter(u => String(u.PIN) !== String(pinToDelete));
  writeSheetRows('Usuarios', ['Nombre', 'Tipo', 'CasaAsignada', 'PIN', 'Estado'], list);
  return { success: true, message: 'PIN eliminado.' };
}

function setupInitialDataForSs(ss) {
  var casasHeaders = ['ID', 'Nombre', 'Direccion', 'Anfitrion', 'Facilitador', 'Telefono', 'Dia', 'Horario', 'Estado'];
  var casasData = [
    { ID: 'C101', Nombre: 'Casa Betania', Direccion: 'Av. Las Palmas 420, Col. Centro', Anfitrion: 'Carlos Mendoza', Facilitador: 'David Silva', Telefono: '5551234567', Dia: 'Miércoles', Horario: '19:30', Estado: 'Activo' },
    { ID: 'C102', Nombre: 'Casa Shalom', Direccion: 'Calle Los Olivos 88, Col. Jardines', Anfitrion: 'Elena Gómez', Facilitador: 'María López', Telefono: '5559876543', Dia: 'Jueves', Horario: '20:00', Estado: 'Activo' },
    { ID: 'C103', Nombre: 'Casa Nueva Esperanza', Direccion: 'Calle del Sol 15, Col. Vista Hermosa', Anfitrion: 'Roberto Ruiz', Facilitador: 'Jorge Ramos', Telefono: '5554567890', Dia: 'Viernes', Horario: '19:00', Estado: 'Activo' }
  ];
  writeRowsToSs(ss, 'Casas', casasHeaders, casasData);

  var intHeaders = ['ID', 'Nombre', 'Telefono', 'CasaAsignada', 'FechaRegistro', 'Estado'];
  var intData = [
    { ID: 'I1001', Nombre: 'Ana Martínez', Telefono: '5551112233', CasaAsignada: 'C101', FechaRegistro: '2026-01-10', Estado: 'Activo' },
    { ID: 'I1002', Nombre: 'Luis Hernández', Telefono: '5552223344', CasaAsignada: 'C101', FechaRegistro: '2026-01-12', Estado: 'Activo' },
    { ID: 'I1003', Nombre: 'Sofía Torres', Telefono: '5553334455', CasaAsignada: 'C102', FechaRegistro: '2026-01-15', Estado: 'Activo' },
    { ID: 'I1004', Nombre: 'Pedro Ramírez', Telefono: '5554445566', CasaAsignada: 'C102', FechaRegistro: '2026-01-20', Estado: 'Activo' },
    { ID: 'I1005', Nombre: 'Lucía Morales', Telefono: '5555556677', CasaAsignada: 'C103', FechaRegistro: '2026-02-01', Estado: 'Activo' },
    { ID: 'I1006', Nombre: 'Gabriel Castro', Telefono: '5556667788', CasaAsignada: 'C103', FechaRegistro: '2026-02-05', Estado: 'Activo' }
  ];
  writeRowsToSs(ss, 'Integrantes', intHeaders, intData);

  var matHeaders = ['ID', 'Titulo', 'Descripcion', 'Fecha', 'EnlaceDrive', 'Estado'];
  var matData = [
    { ID: 'M201', Titulo: 'Estudio 1: La Fe en Familia', Descripcion: 'Guía de preguntas y pasajes bíblicos para la semana.', Fecha: '2026-02-08', EnlaceDrive: 'https://drive.google.com', Estado: 'Activo' },
    { ID: 'M202', Titulo: 'Estudio 2: Caminar en Comunidad', Descripcion: 'Material sobre el servicio mutuo y hospitalidad.', Fecha: '2026-02-15', EnlaceDrive: 'https://drive.google.com', Estado: 'Activo' }
  ];
  writeRowsToSs(ss, 'Materiales', matHeaders, matData);

  var usrHeaders = ['Nombre', 'Tipo', 'CasaAsignada', 'PIN', 'Estado'];
  var usrData = [
    { Nombre: 'Pastor / Admin', Tipo: 'admin', CasaAsignada: '', PIN: '1234', Estado: 'Activo' },
    { Nombre: 'Carlos Mendoza', Tipo: 'anfitrion', CasaAsignada: 'C101', PIN: '1001', Estado: 'Activo' },
    { Nombre: 'Elena Gómez', Tipo: 'anfitrion', CasaAsignada: 'C102', PIN: '1002', Estado: 'Activo' },
    { Nombre: 'Roberto Ruiz', Tipo: 'anfitrion', CasaAsignada: 'C103', PIN: '1003', Estado: 'Activo' }
  ];
  writeRowsToSs(ss, 'Usuarios', usrHeaders, usrData);
}

function setupSampleData() {
  var ss = getSpreadsheet();
  setupInitialDataForSs(ss);
  return "¡Datos iniciales sembrados con éxito!";
}
