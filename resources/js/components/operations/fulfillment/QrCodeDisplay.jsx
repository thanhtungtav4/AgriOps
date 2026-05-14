import React from 'react';

const QrCodeDisplay = ({ qrCode, size = 'w-24 h-24' }) => {
  if (!qrCode) {
    return <div className="bg-gray-100 border rounded flex items-center justify-center w-24 h-24">
      <span className="text-gray-500 text-xs text-center px-1">No QR Code</span>
    </div>;
  }

  // Check if qrCode is a URL or base64 encoded image
  const isImageUrl = qrCode.startsWith('http') || qrCode.startsWith('data:image');

  return (
    <div className={`${size} border rounded overflow-hidden`}>
      {isImageUrl ? (
        <img 
          src={qrCode} 
          alt="QR Code" 
          className="w-full h-full object-contain"
        />
      ) : (
        <div className="bg-gray-100 flex items-center justify-center h-full">
          <span className="text-xs text-center px-1 break-words">{qrCode}</span>
        </div>
      )}
    </div>
  );
};

export default QrCodeDisplay;