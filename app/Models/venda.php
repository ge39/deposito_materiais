// Venda.php
public function cliente() {
    return $this->belongsTo(Cliente::class);
}

public function itens() {
    return $this->hasMany(ItemVenda::class);
}

// Entrega.php
public function venda() {
    return $this->belongsTo(Venda::class);
}

public function frota() {
    return $this->belongsTo(Frota::class);
}

public function motorista() {
    return $this->belongsTo(Motorista::class);
}

// Usuario.php
public function funcionario() {
    return $this->belongsTo(Funcionario::class);
}
