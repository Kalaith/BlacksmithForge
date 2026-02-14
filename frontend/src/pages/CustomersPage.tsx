import React from 'react';
import CustomersTab from '../components/features/CustomersTab';

const CustomersPage: React.FC = () => {
  // If routing is used, you may want to handle tab/page activation here
  return (
    <main className="page-content">
      <CustomersTab active={true} />
    </main>
  );
};

export default CustomersPage;
