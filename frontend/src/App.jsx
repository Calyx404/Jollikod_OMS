import { useEffect, useState } from "react";
import { Container, Table } from "react-bootstrap";

function App() {
  const [users, setUsers] = useState([]);

  useEffect(() => {
    fetch("http://localhost:8000/api/users.php")
      .then((res) => res.json())
      .then((data) => setUsers(data));
  }, []);

  return (
    <Container className="mt-5">
      <h2 className="mb-3 text-primary">User List (React + PHP API)</h2>
      <Table striped bordered hover>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
          </tr>
        </thead>
        <tbody>
          {users.map((u) => (
            <tr key={u.id}>
              <td>{u.id}</td>
              <td>{u.name}</td>
            </tr>
          ))}
        </tbody>
      </Table>
    </Container>
  );
}

export default App;
