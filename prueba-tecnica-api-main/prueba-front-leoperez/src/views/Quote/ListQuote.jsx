import React, { Component } from 'react';

class ListQuote extends Component {
    getListQuotes() {
        fetch('http://localhost:8000/quote')
            .then((response) => response.json())
            .then((data) => {
                this.setState({quotes: data});
            })
            .catch ((error) => {
                console.error('Error al obtener cotizaciones', error);
            });
    }
    render() {
        return (
            <div>
                <h2>LISTADO DE COTIZACIONES</h2>
                <span>
                    <button>CREAR</button>
                </span>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {quote.map((quote) => (
                            <tr key={quote.quote_id}>
                                <td>{quote.quote_id}</td>
                                <td>{quote.quote_modified_at}</td>
                                <td>{quote.created_by}</td>
                                <td>{quote.quote_total}</td>
                                <td>
                                    <button>Ver</button>
                                    <button>Descargar</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        );
    }
}

export default ListQuote;