import React, { useState, useEffect } from 'react';
import api from '../../../services/api';
import QrCodeDisplay from '../../../components/operations/fulfillment/QrCodeDisplay';

const FulfillmentFlow = () => {
  // State for all entities
  const [inspections, setInspections] = useState([]);
  const [harvestLots, setHarvestLots] = useState([]);
  const [packingLots, setPackingLots] = useState([]);
  const [deliveries, setDeliveries] = useState([]);
  const [returns, setReturns] = useState([]);
  
  // Loading states
  const [loading, setLoading] = useState({ inspections: false, harvestLots: false, packingLots: false, deliveries: false, returns: false });
  const [activeTab, setActiveTab] = useState('dashboard');
  
  // Form states
  const [showCreateInspectionForm, setShowCreateInspectionForm] = useState(false);
  const [showCreateHarvestForm, setShowCreateHarvestForm] = useState(false);
  const [showCreatePackingForm, setShowCreatePackingForm] = useState(false);
  const [showCreateDeliveryForm, setShowCreateDeliveryForm] = useState(false);
  const [showCreateReturnForm, setShowCreateReturnForm] = useState(false);
  
  // Form data
  const [inspectionData, setInspectionData] = useState({
    planting_batch_id: '',
    inspected_at: new Date().toISOString().split('T')[0],
    checklist: [
      { key: 'maturity', label: 'Maturity', passed: true },
      { key: 'pest_free', label: 'No active pest pressure', passed: true },
      { key: 'isolation_reviewed', label: 'Isolation reviewed', passed: true }
    ],
    notes: ''
  });
  
  const [harvestData, setHarvestData] = useState({
    planting_batch_id: '',
    harvest_date: new Date().toISOString().split('T')[0],
    raw_quantity: '',
    unit: 'kg',
    grade_a_quantity: '',
    grade_b_quantity: '',
    grade_c_quantity: '',
    reject_quantity: '',
    notes: ''
  });
  
  const [packingData, setPackingData] = useState({
    farm_id: '',
    packed_at: new Date().toISOString().split('T')[0],
    total_output_quantity: '',
    unit: 'kg',
    sources: [{ harvest_lot_id: '', quantity: '' }],
    notes: ''
  });
  
  const [deliveryData, setDeliveryData] = useState({
    packing_lot_id: '',
    delivered_at: new Date().toISOString().split('T')[0],
    planned_quantity: '',
    accepted_quantity: '',
    unit: 'kg',
    unit_price: '',
    customer_name: ''
  });
  
  const [returnData, setReturnData] = useState({
    delivery_note_id: '',
    returned_at: new Date().toISOString().split('T')[0],
    quantity: '',
    unit: 'kg',
    reason: 'bruised_wilted',
    handling_action: 'record_loss',
    notes: ''
  });

  // Load data for all entities
  useEffect(() => {
    loadData();
  }, []);
  
  const loadData = async () => {
    setLoading(prev => ({ ...prev, inspections: true }));
    try {
      const response = await api.get('/pre-harvest-inspections');
      setInspections(response.data.data);
    } catch (error) {
      console.error('Error loading inspections:', error);
    } finally {
      setLoading(prev => ({ ...prev, inspections: false }));
    }
    
    setLoading(prev => ({ ...prev, harvestLots: true }));
    try {
      const response = await api.get('/harvest-lots');
      setHarvestLots(response.data.data);
    } catch (error) {
      console.error('Error loading harvest lots:', error);
    } finally {
      setLoading(prev => ({ ...prev, harvestLots: false }));
    }
    
    setLoading(prev => ({ ...prev, packingLots: true }));
    try {
      const response = await api.get('/packing-lots');
      setPackingLots(response.data.data);
    } catch (error) {
      console.error('Error loading packing lots:', error);
    } finally {
      setLoading(prev => ({ ...prev, packingLots: false }));
    }
    
    setLoading(prev => ({ ...prev, deliveries: true }));
    try {
      const response = await api.get('/deliveries');
      setDeliveries(response.data.data);
    } catch (error) {
      console.error('Error loading deliveries:', error);
    } finally {
      setLoading(prev => ({ ...prev, deliveries: false }));
    }
    
    setLoading(prev => ({ ...prev, returns: true }));
    try {
      const response = await api.get('/returns');
      setReturns(response.data.data);
    } catch (error) {
      console.error('Error loading returns:', error);
    } finally {
      setLoading(prev => ({ ...prev, returns: false }));
    }
  };

  // Create inspection
  const handleCreateInspection = async (e) => {
    e.preventDefault();
    try {
      const response = await api.post('/pre-harvest-inspections', inspectionData);
      setInspections(prev => [...prev, response.data.data]);
      setShowCreateInspectionForm(false);
      setInspectionData({
        planting_batch_id: '',
        inspected_at: new Date().toISOString().split('T')[0],
        checklist: [
          { key: 'maturity', label: 'Maturity', passed: true },
          { key: 'pest_free', label: 'No active pest pressure', passed: true },
          { key: 'isolation_reviewed', label: 'Isolation reviewed', passed: true }
        ],
        notes: ''
      });
    } catch (error) {
      console.error('Error creating inspection:', error);
    }
  };
  
  // Approve inspection
  const handleApproveInspection = async (id) => {
    try {
      const response = await api.post(`/pre-harvest-inspections/${id}/approve`);
      setInspections(prev => prev.map(insp => insp.id === id ? response.data.data : insp));
    } catch (error) {
      console.error('Error approving inspection:', error);
    }
  };
  
  // Create harvest lot
  const handleCreateHarvest = async (e) => {
    e.preventDefault();
    try {
      const response = await api.post('/harvest-lots', {
        ...harvestData,
        raw_quantity: parseFloat(harvestData.raw_quantity),
        grade_a_quantity: parseFloat(harvestData.grade_a_quantity) || 0,
        grade_b_quantity: parseFloat(harvestData.grade_b_quantity) || 0,
        grade_c_quantity: parseFloat(harvestData.grade_c_quantity) || 0,
        reject_quantity: parseFloat(harvestData.reject_quantity) || 0
      });
      setHarvestLots(prev => [...prev, response.data.data]);
      setShowCreateHarvestForm(false);
      setHarvestData({
        planting_batch_id: '',
        harvest_date: new Date().toISOString().split('T')[0],
        raw_quantity: '',
        unit: 'kg',
        grade_a_quantity: '',
        grade_b_quantity: '',
        grade_c_quantity: '',
        reject_quantity: '',
        notes: ''
      });
    } catch (error) {
      console.error('Error creating harvest lot:', error);
    }
  };
  
  // Create packing lot
  const handleCreatePacking = async (e) => {
    e.preventDefault();
    try {
      const response = await api.post('/packing-lots', {
        ...packingData,
        total_output_quantity: parseFloat(packingData.total_output_quantity),
        sources: packingData.sources.map(source => ({
          harvest_lot_id: parseInt(source.harvest_lot_id),
          quantity: parseFloat(source.quantity)
        }))
      });
      setPackingLots(prev => [...prev, response.data.data]);
      setShowCreatePackingForm(false);
      setPackingData({
        farm_id: '',
        packed_at: new Date().toISOString().split('T')[0],
        total_output_quantity: '',
        unit: 'kg',
        sources: [{ harvest_lot_id: '', quantity: '' }],
        notes: ''
      });
    } catch (error) {
      console.error('Error creating packing lot:', error);
    }
  };
  
  // Add source to packing form
  const addPackingSource = () => {
    setPackingData(prev => ({
      ...prev,
      sources: [...prev.sources, { harvest_lot_id: '', quantity: '' }]
    }));
  };
  
  // Remove source from packing form
  const removePackingSource = (index) => {
    setPackingData(prev => ({
      ...prev,
      sources: prev.sources.filter((_, i) => i !== index)
    }));
  };

  const traceabilityUrl = (qrCode) => `${window.location.origin}/traceability/${encodeURIComponent(qrCode)}`;

  const copyTraceabilityUrl = async (qrCode) => {
    if (!qrCode) return;

    try {
      await navigator.clipboard.writeText(traceabilityUrl(qrCode));
    } catch (error) {
      console.error('Error copying traceability URL:', error);
    }
  };
  
  // Update packing source
  const updatePackingSource = (index, field, value) => {
    setPackingData(prev => {
      const newSources = [...prev.sources];
      newSources[index][field] = value;
      return { ...prev, sources: newSources };
    });
  };
  
  // Create delivery
  const handleCreateDelivery = async (e) => {
    e.preventDefault();
    try {
      const response = await api.post('/deliveries', {
        ...deliveryData,
        planned_quantity: parseFloat(deliveryData.planned_quantity),
        accepted_quantity: parseFloat(deliveryData.accepted_quantity),
        unit_price: parseFloat(deliveryData.unit_price)
      });
      setDeliveries(prev => [...prev, response.data.data]);
      setShowCreateDeliveryForm(false);
      setDeliveryData({
        packing_lot_id: '',
        delivered_at: new Date().toISOString().split('T')[0],
        planned_quantity: '',
        accepted_quantity: '',
        unit: 'kg',
        unit_price: '',
        customer_name: ''
      });
    } catch (error) {
      console.error('Error creating delivery:', error);
    }
  };
  
  // Create return
  const handleCreateReturn = async (e) => {
    e.preventDefault();
    try {
      const response = await api.post('/returns', {
        ...returnData,
        quantity: parseFloat(returnData.quantity)
      });
      setReturns(prev => [...prev, response.data.data]);
      setShowCreateReturnForm(false);
      setReturnData({
        delivery_note_id: '',
        returned_at: new Date().toISOString().split('T')[0],
        quantity: '',
        unit: 'kg',
        reason: 'bruised_wilted',
        handling_action: 'record_loss',
        notes: ''
      });
    } catch (error) {
      console.error('Error creating return:', error);
    }
  };

  // Render tab content based on active tab
  const renderTabContent = () => {
    switch (activeTab) {
      case 'dashboard':
        return (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {/* Dashboard cards showing counts */}
            <div className="bg-white p-6 rounded-lg shadow">
              <h3 className="text-lg font-semibold mb-2">Inspections</h3>
              <p className="text-3xl font-bold">{inspections.length}</p>
            </div>
            <div className="bg-white p-6 rounded-lg shadow">
              <h3 className="text-lg font-semibold mb-2">Harvest Lots</h3>
              <p className="text-3xl font-bold">{harvestLots.length}</p>
            </div>
            <div className="bg-white p-6 rounded-lg shadow">
              <h3 className="text-lg font-semibold mb-2">Packing Lots</h3>
              <p className="text-3xl font-bold">{packingLots.length}</p>
            </div>
            <div className="bg-white p-6 rounded-lg shadow">
              <h3 className="text-lg font-semibold mb-2">Deliveries</h3>
              <p className="text-3xl font-bold">{deliveries.length}</p>
            </div>
            <div className="bg-white p-6 rounded-lg shadow">
              <h3 className="text-lg font-semibold mb-2">Returns</h3>
              <p className="text-3xl font-bold">{returns.length}</p>
            </div>
            
            {/* Quick Stats */}
            <div className="bg-white p-6 rounded-lg shadow">
              <h3 className="text-lg font-semibold mb-2">Approved Inspections</h3>
              <p className="text-3xl font-bold text-green-600">
                {inspections.filter(i => i.status === 'approved').length}
              </p>
            </div>
          </div>
        );
      
      case 'inspections':
        return (
          <div>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-semibold">Pre-Harvest Inspections</h2>
              <button 
                onClick={() => setShowCreateInspectionForm(true)}
                className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
              >
                Create New
              </button>
            </div>
            
            {showCreateInspectionForm && (
              <div className="mb-6 bg-gray-100 p-4 rounded">
                <form onSubmit={handleCreateInspection}>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                      <label className="block text-sm font-medium mb-1">Planting Batch ID</label>
                      <input
                        type="number"
                        value={inspectionData.planting_batch_id}
                        onChange={(e) => setInspectionData({...inspectionData, planting_batch_id: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Inspected At</label>
                      <input
                        type="date"
                        value={inspectionData.inspected_at}
                        onChange={(e) => setInspectionData({...inspectionData, inspected_at: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                  </div>
                  
                  <div className="mb-4">
                    <label className="block text-sm font-medium mb-1">Notes</label>
                    <textarea
                      value={inspectionData.notes}
                      onChange={(e) => setInspectionData({...inspectionData, notes: e.target.value})}
                      className="w-full p-2 border rounded"
                      rows="3"
                    />
                  </div>
                  
                  <div className="mb-4">
                    <label className="block text-sm font-medium mb-1">Checklist</label>
                    {inspectionData.checklist.map((item, index) => (
                      <div key={index} className="flex items-center mb-2">
                        <input
                          type="checkbox"
                          checked={item.passed}
                          onChange={(e) => {
                            const newChecklist = [...inspectionData.checklist];
                            newChecklist[index].passed = e.target.checked;
                            setInspectionData({...inspectionData, checklist: newChecklist});
                          }}
                          className="mr-2"
                        />
                        <span>{item.label}</span>
                      </div>
                    ))}
                  </div>
                  
                  <div className="flex space-x-2">
                    <button type="submit" className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                      Save Inspection
                    </button>
                    <button 
                      type="button" 
                      onClick={() => setShowCreateInspectionForm(false)}
                      className="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
                    >
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            )}
            
            <div className="overflow-x-auto">
              <table className="min-w-full bg-white border">
                <thead>
                  <tr className="bg-gray-100">
                    <th className="py-2 px-4 border">ID</th>
                    <th className="py-2 px-4 border">Batch</th>
                    <th className="py-2 px-4 border">Inspector</th>
                    <th className="py-2 px-4 border">Status</th>
                    <th className="py-2 px-4 border">Date</th>
                    <th className="py-2 px-4 border">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {inspections.map(inspection => (
                    <tr key={inspection.id}>
                      <td className="py-2 px-4 border">{inspection.id}</td>
                      <td className="py-2 px-4 border">{inspection.planting_batch?.id || 'N/A'}</td>
                      <td className="py-2 px-4 border">{inspection.inspector?.name || 'N/A'}</td>
                      <td className="py-2 px-4 border">
                        <span className={`px-2 py-1 rounded text-xs ${
                          inspection.status === 'approved' ? 'bg-green-100 text-green-800' :
                          inspection.status === 'rejected' ? 'bg-red-100 text-red-800' :
                          'bg-yellow-100 text-yellow-800'
                        }`}>
                          {inspection.status}
                        </span>
                      </td>
                      <td className="py-2 px-4 border">{new Date(inspection.inspected_at).toLocaleDateString()}</td>
                      <td className="py-2 px-4 border">
                        {inspection.status === 'submitted' && (
                          <button
                            onClick={() => handleApproveInspection(inspection.id)}
                            className="bg-green-500 text-white px-3 py-1 rounded text-xs mr-2"
                          >
                            Approve
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        );
      
      case 'harvest':
        return (
          <div>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-semibold">Harvest Lots</h2>
              <button 
                onClick={() => setShowCreateHarvestForm(true)}
                className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
              >
                Create New
              </button>
            </div>
            
            {showCreateHarvestForm && (
              <div className="mb-6 bg-gray-100 p-4 rounded">
                <form onSubmit={handleCreateHarvest}>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                      <label className="block text-sm font-medium mb-1">Planting Batch ID</label>
                      <input
                        type="number"
                        value={harvestData.planting_batch_id}
                        onChange={(e) => setHarvestData({...harvestData, planting_batch_id: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Harvest Date</label>
                      <input
                        type="date"
                        value={harvestData.harvest_date}
                        onChange={(e) => setHarvestData({...harvestData, harvest_date: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Raw Quantity</label>
                      <input
                        type="number"
                        step="0.001"
                        value={harvestData.raw_quantity}
                        onChange={(e) => setHarvestData({...harvestData, raw_quantity: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Unit</label>
                      <select
                        value={harvestData.unit}
                        onChange={(e) => setHarvestData({...harvestData, unit: e.target.value})}
                        className="w-full p-2 border rounded"
                      >
                        <option value="kg">kg</option>
                        <option value="trái">trái</option>
                        <option value="cây">cây</option>
                      </select>
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Grade A Quantity</label>
                      <input
                        type="number"
                        step="0.001"
                        value={harvestData.grade_a_quantity}
                        onChange={(e) => setHarvestData({...harvestData, grade_a_quantity: e.target.value})}
                        className="w-full p-2 border rounded"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Grade B Quantity</label>
                      <input
                        type="number"
                        step="0.001"
                        value={harvestData.grade_b_quantity}
                        onChange={(e) => setHarvestData({...harvestData, grade_b_quantity: e.target.value})}
                        className="w-full p-2 border rounded"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Grade C Quantity</label>
                      <input
                        type="number"
                        step="0.001"
                        value={harvestData.grade_c_quantity}
                        onChange={(e) => setHarvestData({...harvestData, grade_c_quantity: e.target.value})}
                        className="w-full p-2 border rounded"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Reject Quantity</label>
                      <input
                        type="number"
                        step="0.001"
                        value={harvestData.reject_quantity}
                        onChange={(e) => setHarvestData({...harvestData, reject_quantity: e.target.value})}
                        className="w-full p-2 border rounded"
                      />
                    </div>
                  </div>
                  
                  <div className="mb-4">
                    <label className="block text-sm font-medium mb-1">Notes</label>
                    <textarea
                      value={harvestData.notes}
                      onChange={(e) => setHarvestData({...harvestData, notes: e.target.value})}
                      className="w-full p-2 border rounded"
                      rows="3"
                    />
                  </div>
                  
                  <div className="flex space-x-2">
                    <button type="submit" className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                      Save Harvest
                    </button>
                    <button 
                      type="button" 
                      onClick={() => setShowCreateHarvestForm(false)}
                      className="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
                    >
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            )}
            
            <div className="overflow-x-auto">
              <table className="min-w-full bg-white border">
                <thead>
                  <tr className="bg-gray-100">
                    <th className="py-2 px-4 border">ID</th>
                    <th className="py-2 px-4 border">Batch</th>
                    <th className="py-2 px-4 border">Raw Qty</th>
                    <th className="py-2 px-4 border">Grade A</th>
                    <th className="py-2 px-4 border">Grade B</th>
                    <th className="py-2 px-4 border">Grade C</th>
                    <th className="py-2 px-4 border">Status</th>
                    <th className="py-2 px-4 border">Date</th>
                  </tr>
                </thead>
                <tbody>
                  {harvestLots.map(lot => (
                    <tr key={lot.id}>
                      <td className="py-2 px-4 border">{lot.id}</td>
                      <td className="py-2 px-4 border">{lot.planting_batch?.id || 'N/A'}</td>
                      <td className="py-2 px-4 border">{lot.raw_quantity} {lot.unit}</td>
                      <td className="py-2 px-4 border">{lot.grade_a_quantity || 0} {lot.unit}</td>
                      <td className="py-2 px-4 border">{lot.grade_b_quantity || 0} {lot.unit}</td>
                      <td className="py-2 px-4 border">{lot.grade_c_quantity || 0} {lot.unit}</td>
                      <td className="py-2 px-4 border">
                        <span className={`px-2 py-1 rounded text-xs ${
                          lot.status === 'available' ? 'bg-green-100 text-green-800' :
                          lot.status === 'packed' ? 'bg-blue-100 text-blue-800' :
                          'bg-gray-100 text-gray-800'
                        }`}>
                          {lot.status}
                        </span>
                      </td>
                      <td className="py-2 px-4 border">{new Date(lot.harvest_date).toLocaleDateString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        );
      
      case 'packing':
        return (
          <div>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-semibold">Packing Lots</h2>
              <button 
                onClick={() => setShowCreatePackingForm(true)}
                className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
              >
                Create New
              </button>
            </div>
            
            {showCreatePackingForm && (
              <div className="mb-6 bg-gray-100 p-4 rounded">
                <form onSubmit={handleCreatePacking}>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                      <label className="block text-sm font-medium mb-1">Farm ID</label>
                      <input
                        type="number"
                        value={packingData.farm_id}
                        onChange={(e) => setPackingData({...packingData, farm_id: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Packed At</label>
                      <input
                        type="date"
                        value={packingData.packed_at}
                        onChange={(e) => setPackingData({...packingData, packed_at: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Total Output Quantity</label>
                      <input
                        type="number"
                        step="0.001"
                        value={packingData.total_output_quantity}
                        onChange={(e) => setPackingData({...packingData, total_output_quantity: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Unit</label>
                      <select
                        value={packingData.unit}
                        onChange={(e) => setPackingData({...packingData, unit: e.target.value})}
                        className="w-full p-2 border rounded"
                      >
                        <option value="kg">kg</option>
                        <option value="trái">trái</option>
                        <option value="cây">cây</option>
                      </select>
                    </div>
                  </div>
                  
                  <div className="mb-4">
                    <label className="block text-sm font-medium mb-2">Sources</label>
                    {packingData.sources.map((source, index) => (
                      <div key={index} className="grid grid-cols-12 gap-2 mb-2">
                        <div className="col-span-5">
                          <input
                            type="number"
                            placeholder="Harvest Lot ID"
                            value={source.harvest_lot_id}
                            onChange={(e) => updatePackingSource(index, 'harvest_lot_id', e.target.value)}
                            className="w-full p-2 border rounded"
                          />
                        </div>
                        <div className="col-span-5">
                          <input
                            type="number"
                            step="0.001"
                            placeholder="Quantity"
                            value={source.quantity}
                            onChange={(e) => updatePackingSource(index, 'quantity', e.target.value)}
                            className="w-full p-2 border rounded"
                          />
                        </div>
                        <div className="col-span-2">
                          <button
                            type="button"
                            onClick={() => removePackingSource(index)}
                            className="w-full bg-red-500 text-white p-2 rounded"
                            disabled={packingData.sources.length <= 1}
                          >
                            -
                          </button>
                        </div>
                      </div>
                    ))}
                    <button
                      type="button"
                      onClick={addPackingSource}
                      className="mt-2 bg-green-500 text-white px-3 py-1 rounded"
                    >
                      Add Source +
                    </button>
                  </div>
                  
                  <div className="mb-4">
                    <label className="block text-sm font-medium mb-1">Notes</label>
                    <textarea
                      value={packingData.notes}
                      onChange={(e) => setPackingData({...packingData, notes: e.target.value})}
                      className="w-full p-2 border rounded"
                      rows="3"
                    />
                  </div>
                  
                  <div className="flex space-x-2">
                    <button type="submit" className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                      Save Packing
                    </button>
                    <button 
                      type="button" 
                      onClick={() => setShowCreatePackingForm(false)}
                      className="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
                    >
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            )}
            
            <div className="overflow-x-auto">
              <table className="min-w-full bg-white border">
                <thead>
                  <tr className="bg-gray-100">
                    <th className="py-2 px-4 border">ID</th>
                    <th className="py-2 px-4 border">Code</th>
                    <th className="py-2 px-4 border">Output Qty</th>
                    <th className="py-2 px-4 border">QR Code</th>
                    <th className="py-2 px-4 border">Sources Count</th>
                    <th className="py-2 px-4 border">Status</th>
                    <th className="py-2 px-4 border">Date</th>
                    <th className="py-2 px-4 border">Trace</th>
                  </tr>
                </thead>
                <tbody>
                  {packingLots.map(lot => (
                    <tr key={lot.id}>
                      <td className="py-2 px-4 border">{lot.id}</td>
                      <td className="py-2 px-4 border">{lot.code || 'N/A'}</td>
                      <td className="py-2 px-4 border">{lot.total_output_quantity} {lot.unit}</td>
                      <td className="py-2 px-4 border">
                        {lot.qr_code && (
                          <QrCodeDisplay qrCode={lot.qr_code} size="w-16 h-16" />
                        )}
                      </td>
                      <td className="py-2 px-4 border">{lot.sources?.length || 0}</td>
                      <td className="py-2 px-4 border">
                        <span className={`px-2 py-1 rounded text-xs ${
                          lot.status === 'packed' ? 'bg-blue-100 text-blue-800' :
                          lot.status === 'published' ? 'bg-green-100 text-green-800' :
                          'bg-gray-100 text-gray-800'
                        }`}>
                          {lot.status}
                        </span>
                      </td>
                      <td className="py-2 px-4 border">{new Date(lot.packed_at).toLocaleDateString()}</td>
                      <td className="py-2 px-4 border">
                        {lot.qr_code ? (
                          <div className="flex flex-col space-y-1">
                            <a
                              href={traceabilityUrl(lot.qr_code)}
                              target="_blank"
                              rel="noreferrer"
                              className="text-blue-600 hover:text-blue-800 text-xs"
                            >
                              Open public
                            </a>
                            <button
                              type="button"
                              onClick={() => copyTraceabilityUrl(lot.qr_code)}
                              className="text-emerald-600 hover:text-emerald-800 text-xs text-left"
                            >
                              Copy link
                            </button>
                          </div>
                        ) : (
                          <span className="text-xs text-gray-500">No QR</span>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        );
      
      case 'delivery':
        return (
          <div>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-semibold">Deliveries</h2>
              <button 
                onClick={() => setShowCreateDeliveryForm(true)}
                className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
              >
                Create New
              </button>
            </div>
            
            {showCreateDeliveryForm && (
              <div className="mb-6 bg-gray-100 p-4 rounded">
                <form onSubmit={handleCreateDelivery}>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                      <label className="block text-sm font-medium mb-1">Packing Lot ID</label>
                      <input
                        type="number"
                        value={deliveryData.packing_lot_id}
                        onChange={(e) => setDeliveryData({...deliveryData, packing_lot_id: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Delivered At</label>
                      <input
                        type="date"
                        value={deliveryData.delivered_at}
                        onChange={(e) => setDeliveryData({...deliveryData, delivered_at: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Planned Quantity</label>
                      <input
                        type="number"
                        step="0.001"
                        value={deliveryData.planned_quantity}
                        onChange={(e) => setDeliveryData({...deliveryData, planned_quantity: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Accepted Quantity</label>
                      <input
                        type="number"
                        step="0.001"
                        value={deliveryData.accepted_quantity}
                        onChange={(e) => setDeliveryData({...deliveryData, accepted_quantity: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Unit</label>
                      <select
                        value={deliveryData.unit}
                        onChange={(e) => setDeliveryData({...deliveryData, unit: e.target.value})}
                        className="w-full p-2 border rounded"
                      >
                        <option value="kg">kg</option>
                        <option value="trái">trái</option>
                        <option value="cây">cây</option>
                      </select>
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Unit Price</label>
                      <input
                        type="number"
                        step="0.01"
                        value={deliveryData.unit_price}
                        onChange={(e) => setDeliveryData({...deliveryData, unit_price: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div className="md:col-span-2">
                      <label className="block text-sm font-medium mb-1">Customer Name</label>
                      <input
                        type="text"
                        value={deliveryData.customer_name}
                        onChange={(e) => setDeliveryData({...deliveryData, customer_name: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                  </div>
                  
                  <div className="flex space-x-2">
                    <button type="submit" className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                      Save Delivery
                    </button>
                    <button 
                      type="button" 
                      onClick={() => setShowCreateDeliveryForm(false)}
                      className="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
                    >
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            )}
            
            <div className="overflow-x-auto">
              <table className="min-w-full bg-white border">
                <thead>
                  <tr className="bg-gray-100">
                    <th className="py-2 px-4 border">ID</th>
                    <th className="py-2 px-4 border">Packing Lot</th>
                    <th className="py-2 px-4 border">Customer</th>
                    <th className="py-2 px-4 border">Planned Qty</th>
                    <th className="py-2 px-4 border">Accepted Qty</th>
                    <th className="py-2 px-4 border">Revenue</th>
                    <th className="py-2 px-4 border">Date</th>
                  </tr>
                </thead>
                <tbody>
                  {deliveries.map(delivery => (
                    <tr key={delivery.id}>
                      <td className="py-2 px-4 border">{delivery.id}</td>
                      <td className="py-2 px-4 border">{delivery.packing_lot?.id || 'N/A'}</td>
                      <td className="py-2 px-4 border">{delivery.customer_name}</td>
                      <td className="py-2 px-4 border">{delivery.planned_quantity} {delivery.unit}</td>
                      <td className="py-2 px-4 border">{delivery.accepted_quantity} {delivery.unit}</td>
                      <td className="py-2 px-4 border">{new Intl.NumberFormat().format(delivery.gross_revenue)} VND</td>
                      <td className="py-2 px-4 border">{new Date(delivery.delivered_at).toLocaleDateString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        );
      
      case 'returns':
        return (
          <div>
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-semibold">Returns</h2>
              <button 
                onClick={() => setShowCreateReturnForm(true)}
                className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
              >
                Create New
              </button>
            </div>
            
            {showCreateReturnForm && (
              <div className="mb-6 bg-gray-100 p-4 rounded">
                <form onSubmit={handleCreateReturn}>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                      <label className="block text-sm font-medium mb-1">Delivery Note ID</label>
                      <input
                        type="number"
                        value={returnData.delivery_note_id}
                        onChange={(e) => setReturnData({...returnData, delivery_note_id: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Returned At</label>
                      <input
                        type="date"
                        value={returnData.returned_at}
                        onChange={(e) => setReturnData({...returnData, returned_at: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Quantity</label>
                      <input
                        type="number"
                        step="0.001"
                        value={returnData.quantity}
                        onChange={(e) => setReturnData({...returnData, quantity: e.target.value})}
                        className="w-full p-2 border rounded"
                        required
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Unit</label>
                      <select
                        value={returnData.unit}
                        onChange={(e) => setReturnData({...returnData, unit: e.target.value})}
                        className="w-full p-2 border rounded"
                      >
                        <option value="kg">kg</option>
                        <option value="trái">trái</option>
                        <option value="cây">cây</option>
                      </select>
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Reason</label>
                      <select
                        value={returnData.reason}
                        onChange={(e) => setReturnData({...returnData, reason: e.target.value})}
                        className="w-full p-2 border rounded"
                      >
                        <option value="bruised_wilted">Dập héo</option>
                        <option value="wrong_size">Sai kích cỡ</option>
                        <option value="wrong_weight">Sai khối lượng</option>
                        <option value="bad_color">Màu sắc không đạt</option>
                        <option value="not_uniform">Không đồng đều</option>
                        <option value="pest_disease">Sâu bệnh</option>
                        <option value="packaging_error">Lỗi đóng gói</option>
                        <option value="late_delivery">Giao trễ</option>
                        <option value="short_quantity">Thiếu số lượng</option>
                        <option value="other">Khác</option>
                      </select>
                    </div>
                    <div>
                      <label className="block text-sm font-medium mb-1">Handling Action</label>
                      <select
                        value={returnData.handling_action}
                        onChange={(e) => setReturnData({...returnData, handling_action: e.target.value})}
                        className="w-full p-2 border rounded"
                      >
                        <option value="record_loss">Record Loss</option>
                        <option value="process_again">Process Again</option>
                        <option value="sell_elsewhere">Sell Elsewhere</option>
                        <option value="dispose">Dispose</option>
                      </select>
                    </div>
                  </div>
                  
                  <div className="mb-4">
                    <label className="block text-sm font-medium mb-1">Notes</label>
                    <textarea
                      value={returnData.notes}
                      onChange={(e) => setReturnData({...returnData, notes: e.target.value})}
                      className="w-full p-2 border rounded"
                      rows="3"
                    />
                  </div>
                  
                  <div className="flex space-x-2">
                    <button type="submit" className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                      Save Return
                    </button>
                    <button 
                      type="button" 
                      onClick={() => setShowCreateReturnForm(false)}
                      className="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
                    >
                      Cancel
                    </button>
                  </div>
                </form>
              </div>
            )}
            
            <div className="overflow-x-auto">
              <table className="min-w-full bg-white border">
                <thead>
                  <tr className="bg-gray-100">
                    <th className="py-2 px-4 border">ID</th>
                    <th className="py-2 px-4 border">Delivery</th>
                    <th className="py-2 px-4 border">Qty</th>
                    <th className="py-2 px-4 border">Reason</th>
                    <th className="py-2 px-4 border">Action</th>
                    <th className="py-2 px-4 border">Revenue Deduction</th>
                    <th className="py-2 px-4 border">Date</th>
                  </tr>
                </thead>
                <tbody>
                  {returns.map(ret => (
                    <tr key={ret.id}>
                      <td className="py-2 px-4 border">{ret.id}</td>
                      <td className="py-2 px-4 border">{ret.delivery_note?.id || 'N/A'}</td>
                      <td className="py-2 px-4 border">{ret.quantity} {ret.unit}</td>
                      <td className="py-2 px-4 border">{ret.reason}</td>
                      <td className="py-2 px-4 border">{ret.handling_action}</td>
                      <td className="py-2 px-4 border">{new Intl.NumberFormat().format(ret.revenue_deduction)} VND</td>
                      <td className="py-2 px-4 border">{new Date(ret.returned_at).toLocaleDateString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        );
        
      default:
        return null;
    }
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">Fulfillment Flow</h1>
      
      <div className="mb-6">
        <nav className="flex space-x-2 overflow-x-auto">
          <button
            onClick={() => setActiveTab('dashboard')}
            className={`px-4 py-2 rounded ${activeTab === 'dashboard' ? 'bg-blue-500 text-white' : 'bg-gray-200'}`}
          >
            Dashboard
          </button>
          <button
            onClick={() => setActiveTab('inspections')}
            className={`px-4 py-2 rounded ${activeTab === 'inspections' ? 'bg-blue-500 text-white' : 'bg-gray-200'}`}
          >
            Inspections
          </button>
          <button
            onClick={() => setActiveTab('harvest')}
            className={`px-4 py-2 rounded ${activeTab === 'harvest' ? 'bg-blue-500 text-white' : 'bg-gray-200'}`}
          >
            Harvest
          </button>
          <button
            onClick={() => setActiveTab('packing')}
            className={`px-4 py-2 rounded ${activeTab === 'packing' ? 'bg-blue-500 text-white' : 'bg-gray-200'}`}
          >
            Packing
          </button>
          <button
            onClick={() => setActiveTab('delivery')}
            className={`px-4 py-2 rounded ${activeTab === 'delivery' ? 'bg-blue-500 text-white' : 'bg-gray-200'}`}
          >
            Delivery
          </button>
          <button
            onClick={() => setActiveTab('returns')}
            className={`px-4 py-2 rounded ${activeTab === 'returns' ? 'bg-blue-500 text-white' : 'bg-gray-200'}`}
          >
            Returns
          </button>
        </nav>
      </div>
      
      {renderTabContent()}
    </div>
  );
};

export default FulfillmentFlow;
