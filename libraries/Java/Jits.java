package tester.jits.github.com;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.security.InvalidAlgorithmParameterException;
import java.security.InvalidKeyException;
import java.security.Key;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.NoSuchProviderException;
import java.security.SecureRandom;
import java.security.spec.InvalidKeySpecException;
import java.util.Arrays;
import java.util.Base64;
import java.util.List;

import javax.crypto.BadPaddingException;
import javax.crypto.Cipher;
import javax.crypto.IllegalBlockSizeException;
import javax.crypto.KeyGenerator;
import javax.crypto.NoSuchPaddingException;
import javax.crypto.SecretKey;
import javax.crypto.SecretKeyFactory;
import javax.crypto.spec.IvParameterSpec;
import javax.crypto.spec.PBEKeySpec;
import javax.crypto.spec.SecretKeySpec;

import org.json.JSONException;
import org.json.JSONObject;

public class Jits {
	private String server;
	private String connectionKey;
	private String aesKey;
	
	public Jits(String server, String connectionKey, String aesKey){
		this.server = server;
		this.connectionKey = connectionKey;
		this.aesKey = aesKey;
	}
	
	private String fromArrayToJSON(String[] names, float[] values){
		if(names.length != values.length)
			return null;
		
		String res = "{";
		for(int i=0; i<names.length; i++)
			res += "\"" + names[i] + "\" : \"" + values[i] + "\",";
		
		return res.substring(0, res.length()-1) + "}";
		
	}
	
	private String fromListToJSON(List<String> names, List<Float> values){
		if(names.size() != values.size())
			return null;
		
		String res = "{";
		for(int i=0; i<names.size(); i++)
			res += "\"" + names.get(i) + "\" : \"" + values.get(i) + "\",";
		
		return res.substring(0, res.length()-1) + "}";
		
	}
	
	public String encryptJSON(String json, String aesIV){
		try {
			new JSONObject(json);
			
			Key aesk = new SecretKeySpec(aesKey.getBytes("UTF-8"), "AES");
			Cipher cipher = Cipher.getInstance("AES/CBC/NoPadding");

	        byte[] decodedIV = Base64.getDecoder().decode(new String(aesIV).getBytes("UTF-8"));
			cipher.init(Cipher.ENCRYPT_MODE, aesk, new IvParameterSpec(decodedIV));
	        
	        byte[] msg = json.getBytes("UTF-8");
	        int sizeMsg = (((int)msg.length/16)+1)*16;
	        if(((int)msg.length%16) == 0)
	        	sizeMsg = ((int)msg.length/16)*16;
	        byte[] bytesToEncrypt = new byte[sizeMsg];
	        
	        for(int i=0; i<msg.length; i++){
	        	bytesToEncrypt[i] = msg[i];
	        }
	        for(int i=msg.length; i<bytesToEncrypt.length; i++){
	        	bytesToEncrypt[i] = 0x00;
	        }
	        
	        byte[] encrypted = cipher.doFinal(bytesToEncrypt);
	        
		    return Base64.getEncoder().encodeToString(encrypted);
		} catch (NoSuchAlgorithmException | NoSuchPaddingException e) {
			e.printStackTrace();
		} catch (UnsupportedEncodingException e) {
			e.printStackTrace();
		} catch (InvalidKeyException e) {
			e.printStackTrace();
		} catch (InvalidAlgorithmParameterException e) {
			e.printStackTrace();
		} catch (IllegalBlockSizeException e) {
			e.printStackTrace();
		} catch (BadPaddingException e) {
			e.printStackTrace();
		} catch (org.json.JSONException e) {
			e.printStackTrace();
		}
		return null;
	}
	
	public String createPublisherURL(){
		return server + "publisher.php?con=" + connectionKey;
	}
	
	public String createIvURL(){
		return server + "generatorIV.php?con=" + connectionKey;
	}
	
	public boolean sendDataEncript(String encryptedJSON){
		if(encryptedJSON == null)
			return false;
		try {
			HttpURLConnection connection = (HttpURLConnection) new URL(createPublisherURL()).openConnection();
			connection.setRequestMethod("POST");
			connection.setDoOutput(true);
			connection.setRequestProperty("Content-Type", "text/plain");
			connection.setRequestProperty("accept", "application/json");
			
			OutputStreamWriter wr = new OutputStreamWriter(connection.getOutputStream());
			wr.write(new String(encryptedJSON));
			wr.flush();
			
			if (200 <= connection.getResponseCode() && connection.getResponseCode() <= 299) {
				return true;
			} else {
				return false;
			}
			
		} catch (IOException e) {
			e.printStackTrace();
		}

		return false;
	}
	
	public boolean sendDataJson(String json){
		return sendDataEncript(encryptJSON(json, getIV()));
	}
	
	public boolean sendDataArray(String[] names, float[] values){
		return sendDataJson(fromArrayToJSON(names, values));
	}
	
	public boolean sendDataList(List<String> names, List<Float> values){
		return sendDataJson(fromListToJSON(names, values));
	}
	
	private String getIV(){
		try {
			HttpURLConnection connection = (HttpURLConnection) new URL(createIvURL()).openConnection();
			connection.setRequestMethod("POST");
			connection.setDoOutput(true);
			connection.setRequestProperty("Content-Type", "text/plain");
			connection.setRequestProperty("accept", "application/json");
			
			if (200 <= connection.getResponseCode() && connection.getResponseCode() <= 299) {
				BufferedReader in = new BufferedReader(new InputStreamReader((connection.getInputStream())));
				String iv = in.readLine();
		        in.close();
				return iv;
			} else {
				return null;
			}
			
		} catch (IOException e) {
			e.printStackTrace();
		}

		return null;
	}
}
